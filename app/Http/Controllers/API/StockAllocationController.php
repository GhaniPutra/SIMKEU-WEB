<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockAllocation;
use App\Models\User;
use App\Notifications\StockAllocationDeclined;
use App\Services\StockAllocationWorkflow;
use Illuminate\Http\Request;

class StockAllocationController extends Controller
{
    /**
     * Daftar alokasi stok milik karyawan yang sedang login.
     * GET /api/employees/{employee}/stock/allocations
     */
    public function index(Request $request, User $employee)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'pegawai') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya karyawan yang dapat melihat alokasi stok miliknya.',
            ], 403);
        }

        if ((int) $user->id !== (int) $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat melihat alokasi stok milik sendiri.',
            ], 403);
        }
        abort_unless($user->store_id && $employee->store_id === $user->store_id, 403);

        $allocations = StockAllocation::with(['product', 'owner', 'employee', 'reviewer', 'resolver', 'revisedFrom'])
            ->where('employee_user_id', $employee->id)
            ->whereHas('owner', fn ($query) => $query->where('store_id', $user->store_id))
            ->whereHas('product', fn ($query) => $query->where('store_id', $user->store_id))
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar alokasi stok berhasil diambil.',
            'data' => $allocations,
        ], 200);
    }

    /**
     * Daftar alokasi stok pending milik karyawan yang sedang login.
     * GET /api/stock-allocations/pending
     */
    public function pending(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'pegawai') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya karyawan yang dapat melihat alokasi stok pending miliknya.',
            ], 403);
        }

        $allocations = StockAllocation::with(['product', 'owner', 'employee', 'reviewer', 'resolver', 'revisedFrom'])
            ->where('employee_user_id', $user->id)
            ->whereHas('owner', fn ($query) => $query->where('store_id', $user->store_id))
            ->whereHas('product', fn ($query) => $query->where('store_id', $user->store_id))
            ->where('status', StockAllocation::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar alokasi stok pending berhasil diambil.',
            'data' => $allocations,
        ], 200);
    }

    /**
     * Update status alokasi (accept / decline).
     * POST /api/stock-allocations/{allocation}/review
     */
    public function review(Request $request, StockAllocation $allocation, StockAllocationWorkflow $workflow)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'pegawai') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya karyawan yang dapat mengonfirmasi alokasi stok miliknya.',
            ], 403);
        }

        if ((int) $allocation->employee_user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat mengonfirmasi alokasi stok milik sendiri.',
            ], 403);
        }

        $validated = $request->validate([
            'decision' => ['required', 'in:accept,decline'],
            'confirmed_quantity' => ['exclude_unless:decision,accept', 'required', 'integer', 'min:1'],
            'decline_reason' => ['exclude_unless:decision,decline', 'required', 'string', 'max:500'],
            'actual_received_quantity' => ['exclude_unless:decision,decline', 'required', 'integer', 'min:0'],
            'difference_quantity' => ['nullable', 'integer'],
        ]);

        $allocation = $workflow->reviewByEmployee($user, $allocation->id, $validated);
        if ($validated['decision'] === 'decline') {
            $allocation->owner?->notify(new StockAllocationDeclined($allocation));
        }

        return response()->json([
            'success' => true,
            'message' => $validated['decision'] === 'accept'
                ? 'Alokasi stok diterima dan stok aktif pegawai diperbarui.'
                : 'Selisih dicatat. Stok menunggu keputusan pemilik.',
            'data' => $allocation->load(['product', 'employee', 'owner']),
        ], 200);
    }

    public function needsReview(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission('stock_allocations.review')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pemilik yang dapat melihat alokasi yang memerlukan tinjauan.',
            ], 403);
        }

        $allocations = StockAllocation::with(['product', 'owner', 'employee', 'reviewer', 'revisedFrom'])
            ->where('owner_user_id', $user->id)
            ->whereHas('employee', fn ($query) => $query->where('store_id', $user->store_id))
            ->whereHas('product', fn ($query) => $query->where('store_id', $user->store_id))
            ->whereIn('status', [StockAllocation::STATUS_DECLINED, StockAllocation::STATUS_NEEDS_REVIEW])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar alokasi yang ditolak atau memerlukan tinjauan berhasil diambil.',
            'data' => $allocations,
        ], 200);
    }

    public function ownerFollowUp(Request $request, StockAllocation $allocation, StockAllocationWorkflow $workflow)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermission('stock_allocations.review')) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pemilik yang dapat menindaklanjuti alokasi.',
            ], 403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:accept_actual,resend,cancel'],
            'returned_quantity' => ['required_if:action,cancel', 'integer', 'min:0'],
        ]);

        $result = $workflow->followUpByOwner($user, $allocation->id, $validated);

        return response()->json([
            'success' => true,
            'message' => match ($validated['action']) {
                'accept_actual' => 'Jumlah yang diterima disetujui; pegawai diminta mengonfirmasi stoknya.',
                'resend' => 'Alokasi kekurangan dibuat; pegawai diminta mengonfirmasi stok yang diterima.',
                'cancel' => 'Alokasi dibatalkan dan stok yang kembali dicatat ke stok pemilik.',
            },
            'data' => [
                'allocation' => $result['allocation']->load(['product', 'employee', 'owner', 'reviewer', 'resolver', 'revisedFrom']),
                'resent_allocation' => $result['resent_allocation']?->load(['product', 'employee', 'owner', 'revisedFrom']),
            ],
        ], 200);
    }
}
