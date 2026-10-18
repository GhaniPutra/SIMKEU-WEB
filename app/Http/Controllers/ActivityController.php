<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockAllocation;
use App\Support\PeriodFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        ['period' => $period, 'periodLabel' => $periodLabel, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]
            = PeriodFilter::resolveFromRequest($request);

        $sales = PeriodFilter::apply(
            Sale::query()->join('users', 'sales.user_id', '=', 'users.id')
                ->selectRaw(
                    "'sale' as activity_type, sales.id as record_id, sales.updated_at as activity_date, sales.invoice_number as title, "
                    . "CASE WHEN sales.updated_at > sales.created_at THEN 'Data penjualan diperbarui' "
                    . "WHEN sales.notes IS NOT NULL AND sales.notes <> '' THEN sales.notes ELSE 'Penjualan dicatat' END as description, "
                    . 'sales.total as amount, CASE WHEN sales.updated_at > sales.created_at THEN NULL ELSE users.name END as actor, NULL as related_user_id'
                ),
            'sales.transaction_date',
            $periodStart,
            $periodEnd
        );
        $expenses = PeriodFilter::apply(
            Expense::query()->join('users', 'expenses.user_id', '=', 'users.id')
                ->selectRaw(
                    "'expense' as activity_type, expenses.id as record_id, expenses.updated_at as activity_date, expenses.category as title, "
                    . "CASE WHEN expenses.updated_at > expenses.created_at THEN 'Data pengeluaran diperbarui' "
                    . "ELSE COALESCE(expenses.description, 'Pengeluaran dicatat') END as description, "
                    . 'expenses.amount, CASE WHEN expenses.updated_at > expenses.created_at THEN NULL ELSE users.name END as actor, NULL as related_user_id'
                ),
            'expenses.expense_date',
            $periodStart?->toDateString(),
            $periodEnd?->toDateString()
        );

        $activityQueries = [$sales, $expenses];

        if ($user->hasPermission('products.view')) {
            $products = PeriodFilter::apply(
                Product::query()->selectRaw(
                    "'product' as activity_type, products.id as record_id, products.updated_at as activity_date, products.name as title, "
                    . "CASE WHEN products.created_at = products.updated_at THEN 'Produk baru ditambahkan' ELSE 'Data atau stok produk diperbarui' END as description, "
                    . 'NULL as amount, NULL as actor, NULL as related_user_id'
                ),
                'updated_at',
                $periodStart,
                $periodEnd
            )->where('updated_at', '<=', now());
            $activityQueries[] = $products;
        }

        if ($user->hasPermission('stock_allocations.view')) {
            $allocations = DB::table('stock_allocations as allocations')
                ->join('users as employees', 'employees.id', '=', 'allocations.employee_user_id')
                ->join('products', 'products.id', '=', 'allocations.product_id')
                ->where('employees.store_id', $user->store_id)
                ->where('products.store_id', $user->store_id);
            if ($user->isOwner() || $user->hasPermission('stock_allocations.review')) {
                $allocations->where('allocations.owner_user_id', $user->id);
            } else {
                $allocations->where('allocations.employee_user_id', $user->id);
            }
            $allocations = PeriodFilter::apply(
                $allocations->selectRaw(
                    "'allocation' as activity_type, allocations.id as record_id, allocations.updated_at as activity_date, products.name as title, "
                    . "CASE allocations.status "
                    . "WHEN 'pending' THEN 'Menunggu konfirmasi pegawai' "
                    . "WHEN 'accepted' THEN 'Alokasi dikonfirmasi pegawai' "
                    . "WHEN 'declined' THEN 'Alokasi ditolak pegawai' "
                    . "WHEN 'needs_review' THEN 'Perlu tindak lanjut' "
                    . "WHEN 'cancelled' THEN 'Alokasi dibatalkan' ELSE 'Status alokasi diperbarui' END as description, "
                    . 'allocations.requested_quantity as amount, employees.name as actor, allocations.employee_user_id as related_user_id'
                ),
                'allocations.updated_at',
                $periodStart,
                $periodEnd
            );
            $activityQueries[] = $allocations;
        }

        $activities = array_shift($activityQueries);
        foreach ($activityQueries as $activityQuery) {
            $activities->unionAll($activityQuery);
        }
        $activities = DB::query()
            ->fromSub($activities, 'activities')
            ->orderByDesc('activity_date')
            ->orderByDesc('record_id')
            ->paginate(20)
            ->withQueryString();

        $activities->through(function ($activity) use ($period, $user) {
            $activity->typeLabel = match ($activity->activity_type) {
                'sale' => 'Penjualan',
                'expense' => 'Pengeluaran',
                'product' => 'Produk & stok',
                'allocation' => 'Alokasi stok',
                default => 'Aktivitas',
            };
            $activity->typeClass = match ($activity->activity_type) {
                'sale' => 'type-revenue',
                'expense' => 'type-expense',
                'product' => 'type-product',
                'allocation' => 'type-allocation',
                default => '',
            };
            $activity->detailUrl = match ($activity->activity_type) {
                'sale' => $user->hasPermission('sales.view')
                    ? route('pemasukan.show', ['sale' => $activity->record_id, 'period' => $period])
                    : null,
                'expense' => $user->hasPermission('expenses.view')
                    ? route('pengeluaran.show', ['expense' => $activity->record_id, 'period' => $period])
                    : null,
                'product' => route('products.index', ['period' => $period]),
                'allocation' => $user->hasPermission('employees.sales.view') && $activity->related_user_id
                    ? route('employees.manage', ['employee' => $activity->related_user_id, 'period' => $period])
                    : null,
                default => null,
            };
            $activity->formattedAmount = match ($activity->activity_type) {
                'sale', 'expense' => 'Rp ' . number_format((float) $activity->amount, 0, ',', '.'),
                'allocation' => number_format((int) $activity->amount) . ' unit',
                default => '—',
            };
            $activity->formattedDate = Carbon::parse($activity->activity_date)->format('d M Y, H:i');

            return $activity;
        });

        return view('activities.index', compact('activities', 'periodLabel'));
    }
}
