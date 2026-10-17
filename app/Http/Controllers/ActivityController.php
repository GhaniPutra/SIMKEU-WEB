<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Sale;
use App\Support\PeriodFilter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        ['period' => $period, 'periodLabel' => $periodLabel, 'periodStart' => $periodStart, 'periodEnd' => $periodEnd]
            = PeriodFilter::resolveFromRequest($request);

        $sales = PeriodFilter::apply(
            Sale::query()->join('users', 'sales.user_id', '=', 'users.id')
                ->selectRaw("'sale' as activity_type, sales.id as record_id, sales.transaction_date as activity_date, sales.invoice_number as title, sales.notes as description, sales.total as amount, users.name as actor"),
            'sales.transaction_date',
            $periodStart,
            $periodEnd
        );
        $expenses = PeriodFilter::apply(
            Expense::query()->join('users', 'expenses.user_id', '=', 'users.id')
                ->selectRaw("'expense' as activity_type, expenses.id as record_id, expenses.expense_date as activity_date, expenses.category as title, expenses.description, expenses.amount, users.name as actor"),
            'expenses.expense_date',
            $periodStart?->toDateString(),
            $periodEnd?->toDateString()
        );

        $activities = $sales->unionAll($expenses);
        $activities = DB::query()
            ->fromSub($activities, 'activities')
            ->orderByDesc('activity_date')
            ->orderByDesc('record_id')
            ->paginate(20)
            ->withQueryString();

        $activities->through(function ($activity) use ($period) {
            $activity->detailUrl = $activity->activity_type === 'sale'
                ? route('pemasukan.show', ['sale' => $activity->record_id, 'period' => $period])
                : route('pengeluaran.show', ['expense' => $activity->record_id, 'period' => $period]);
            $activity->formattedDate = Carbon::parse($activity->activity_date)->format('d M Y, H:i');

            return $activity;
        });

        return view('activities.index', compact('activities', 'periodLabel'));
    }
}
