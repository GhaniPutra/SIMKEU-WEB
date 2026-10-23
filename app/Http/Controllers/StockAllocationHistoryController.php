<?php

namespace App\Http\Controllers;

use App\Models\StockAllocation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class StockAllocationHistoryController extends Controller
{
    public function index(Request $request)
    {
        $storeId = $request->user()->store_id;
        abort_unless($storeId, 403);

        $filters = $request->validate([
            'employee_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('store_id', $storeId)->where('role', 'pegawai'),
            ],
            'status' => ['nullable', Rule::in([
                StockAllocation::STATUS_PENDING,
                StockAllocation::STATUS_ACCEPTED,
                StockAllocation::STATUS_DECLINED,
                StockAllocation::STATUS_NEEDS_REVIEW,
                StockAllocation::STATUS_CANCELLED,
                StockMovement::STATUS_COMPLETED,
            ])],
            'type' => ['nullable', Rule::in([
                'allocation',
                StockMovement::TYPE_MANUAL_WITHDRAWAL,
                StockMovement::TYPE_AUTO_RETURN,
            ])],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (! empty($filters['start_date']) && ! empty($filters['end_date']) && $filters['end_date'] < $filters['start_date']) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
            ]);
        }

        $allocationRows = DB::table('stock_allocations as a')
            ->join('users as employees', 'employees.id', '=', 'a.employee_user_id')
            ->join('products', 'products.id', '=', 'a.product_id')
            ->join('users as owners', 'owners.id', '=', 'a.owner_user_id')
            ->leftJoin('users as reviewers', 'reviewers.id', '=', 'a.reviewed_by_user_id')
            ->leftJoin('users as resolvers', 'resolvers.id', '=', 'a.resolved_by_user_id')
            ->where('employees.store_id', $storeId)
            ->where('employees.role', 'pegawai')
            ->where('products.store_id', $storeId)
            ->where('owners.store_id', $storeId)
            ->selectRaw("a.id as record_id, 'allocation' as movement_type, COALESCE(a.resolved_at, a.reviewed_at, a.created_at) as occurred_at, a.requested_quantity as quantity, a.status, employees.name as employee_name, products.name as product_name, products.unit as product_unit, COALESCE(resolvers.name, reviewers.name, owners.name) as actor_name, a.employee_user_id as employee_id, a.product_id")
            ->when(! empty($filters['employee_id']), fn ($query) => $query->where('a.employee_user_id', $filters['employee_id']))
            ->when(! empty($filters['status']) && $filters['status'] !== StockMovement::STATUS_COMPLETED, fn ($query) => $query->where('a.status', $filters['status']))
            ->when(($filters['type'] ?? null) === StockMovement::TYPE_MANUAL_WITHDRAWAL || ($filters['type'] ?? null) === StockMovement::TYPE_AUTO_RETURN || ($filters['status'] ?? null) === StockMovement::STATUS_COMPLETED, fn ($query) => $query->whereRaw('1 = 0'))
            ->when(! empty($filters['start_date']), fn ($query) => $query->whereDate(DB::raw('COALESCE(a.resolved_at, a.reviewed_at, a.created_at)'), '>=', $filters['start_date']))
            ->when(! empty($filters['end_date']), fn ($query) => $query->whereDate(DB::raw('COALESCE(a.resolved_at, a.reviewed_at, a.created_at)'), '<=', $filters['end_date']));

        $movementRows = DB::table('stock_movements as m')
            ->join('users as employees', 'employees.id', '=', 'm.employee_user_id')
            ->join('products', 'products.id', '=', 'm.product_id')
            ->leftJoin('users as actors', 'actors.id', '=', 'm.actor_user_id')
            ->where('m.store_id', $storeId)
            ->where('employees.store_id', $storeId)
            ->where('employees.role', 'pegawai')
            ->where('products.store_id', $storeId)
            ->selectRaw("m.id as record_id, m.movement_type, m.occurred_at, m.quantity, 'completed' as status, employees.name as employee_name, products.name as product_name, products.unit as product_unit, actors.name as actor_name, m.employee_user_id as employee_id, m.product_id")
            ->when(! empty($filters['employee_id']), fn ($query) => $query->where('m.employee_user_id', $filters['employee_id']))
            ->when(! empty($filters['type']), fn ($query) => $query->where('m.movement_type', $filters['type']))
            ->when(! empty($filters['status']) && $filters['status'] !== StockMovement::STATUS_COMPLETED, fn ($query) => $query->whereRaw('1 = 0'))
            ->when(! empty($filters['start_date']), fn ($query) => $query->whereDate('m.occurred_at', '>=', $filters['start_date']))
            ->when(! empty($filters['end_date']), fn ($query) => $query->whereDate('m.occurred_at', '<=', $filters['end_date']));

        $rows = $allocationRows->unionAll($movementRows);
        $allocations = DB::query()->fromSub($rows, 'stock_history')
            ->orderByDesc('occurred_at')
            ->orderByDesc('record_id')
            ->paginate(20)
            ->withQueryString();

        $employees = User::query()
            ->where('store_id', $storeId)
            ->where('role', 'pegawai')
            ->orderBy('name')
            ->get(['id', 'name']);

        $store = $request->user()->store;

        return view('stock_allocations.history', compact('allocations', 'employees', 'filters', 'store'));
    }
}
