<?php

namespace App\Http\Controllers;

use App\Models\StockAllocation;
use App\Models\User;
use Illuminate\Http\Request;
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
            ])],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        if (! empty($filters['start_date']) && ! empty($filters['end_date']) && $filters['end_date'] < $filters['start_date']) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
            ]);
        }

        $allocations = StockAllocation::query()
            ->with(['employee:id,name', 'product:id,name,unit', 'owner:id,name'])
            ->whereHas('employee', fn ($query) => $query->where('store_id', $storeId)->where('role', 'pegawai'))
            ->whereHas('product', fn ($query) => $query->where('store_id', $storeId))
            ->whereHas('owner', fn ($query) => $query->where('store_id', $storeId));

        if (! empty($filters['employee_id'])) {
            $allocations->where('employee_user_id', $filters['employee_id']);
        }
        if (! empty($filters['status'])) {
            $allocations->where('status', $filters['status']);
        }
        if (! empty($filters['start_date'])) {
            $allocations->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $allocations->whereDate('created_at', '<=', $filters['end_date']);
        }

        $allocations = $allocations
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $employees = User::query()
            ->where('store_id', $storeId)
            ->where('role', 'pegawai')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('stock_allocations.history', compact('allocations', 'employees', 'filters'));
    }
}
