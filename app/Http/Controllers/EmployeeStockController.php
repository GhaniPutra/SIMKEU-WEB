<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAllocation;
use App\Models\User;
use App\Services\StockAllocationWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeStockController extends Controller
{
    /**
     * Menampilkan halaman distribusi stok untuk pegawai tertentu.
     */
    public function index(User $employee)
    {
        if ($employee->role !== 'pegawai') {
            return redirect()->route('employees.index')->with('error', 'Hanya dapat mengatur stok untuk pegawai.');
        }

        $products = Product::with(['category', 'users' => function ($query) use ($employee) {
            $query->where('user_id', $employee->id);
        }])->orderBy('name')->paginate(15);

        $pendingAllocations = StockAllocation::with(['product', 'owner', 'employee'])
            ->where('employee_user_id', $employee->id)
            ->where('status', StockAllocation::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get();

        return view('employees.stock', compact('employee', 'products', 'pendingAllocations'));
    }

    public function createAllocation(Request $request, User $employee, StockAllocationWorkflow $workflow)
    {
        $owner = $request->user();
        if (! $owner || $owner->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hanya pemilik yang dapat membuat alokasi stok.'], 403);
        }

        if ($employee->role !== 'pegawai') {
            return response()->json(['success' => false, 'message' => 'Hanya dapat mengatur stok untuk pegawai.'], 403);
        }

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'requested_quantity' => ['required', 'integer', 'min:1'],
        ]);

        $allocation = $workflow->create($owner, $employee, $validated['product_id'], $validated['requested_quantity']);

        return response()->json([
            'success' => true,
            'message' => 'Alokasi stok dibuat; jumlah tersebut dicadangkan dari stok pemilik.',
            'allocation' => $allocation->load(['product', 'employee', 'owner']),
        ], 201);
    }

    public function pendingAllocations(Request $request)
    {
        $owner = $request->user();
        if (! $owner || $owner->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $allocations = StockAllocation::with(['product', 'employee'])
            ->where('owner_user_id', $owner->id)
            ->where('status', StockAllocation::STATUS_PENDING)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (StockAllocation $allocation) => [
                'id' => $allocation->id,
                'status' => $allocation->status,
                'product_id' => $allocation->product_id,
                'employee_id' => $allocation->employee_user_id,
                'requested_quantity' => $allocation->requested_quantity,
                'product_name' => $allocation->product?->name,
                'employee_name' => $allocation->employee?->name,
            ]);

        return response()->json(['success' => true, 'data' => $allocations]);
    }

    public function reviewAllocation(Request $request, StockAllocation $allocation)
    {
        return response()->json([
            'success' => false,
            'message' => 'Keputusan Accept atau Decline harus dilakukan oleh karyawan melalui aplikasi.',
        ], 403);
    }

    /**
     * Mentransfer stok dari master ke pegawai atau sebaliknya.
     */
    public function transfer(Request $request, User $employee, StockAllocationWorkflow $workflow)
    {
        $owner = $request->user();
        if (! $owner || $owner->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Hanya pemilik yang dapat mengelola stok pegawai.'], 403);
        }

        if ($employee->role !== 'pegawai') {
            return response()->json(['success' => false, 'message' => 'Hanya dapat mengatur stok untuk pegawai.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|integer|not_in:0',
        ]);

        if ($validated['amount'] > 0) {
            $allocation = $workflow->create($owner, $employee, $validated['product_id'], $validated['amount']);
            $employeeStock = (int) DB::table('product_user')
                ->where('user_id', $employee->id)
                ->where('product_id', $validated['product_id'])
                ->value('stock');

            return response()->json([
                'success' => true,
                'message' => 'Alokasi dibuat dan menunggu konfirmasi karyawan; stok aktif belum berubah.',
                'master_stock' => $allocation->product->fresh()->stock,
                'employee_stock' => $employeeStock,
                'allocation' => $allocation->load(['product', 'employee', 'owner']),
            ], 201);
        }

        try {
            DB::beginTransaction();

            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $amount = $validated['amount'];

            $pullAmount = abs($amount);
            $decremented = DB::table('product_user')
                ->where('user_id', $employee->id)
                ->where('product_id', $product->id)
                ->where('stock', '>=', $pullAmount)
                ->decrement('stock', $pullAmount, ['updated_at' => now()]);
            if ($decremented === 0) {
                $currentEmployeeStock = (int) DB::table('product_user')
                    ->where('user_id', $employee->id)
                    ->where('product_id', $product->id)
                    ->value('stock');
                throw new \Exception("Stok pegawai tidak mencukupi untuk ditarik. Sisa stok pegawai: {$currentEmployeeStock}");
            }

            $product->increment('stock', $pullAmount);

            DB::commit();

            $updatedEmployeeProduct = $employee->products()->where('product_id', $product->id)->first();
            $newEmployeeStock = $updatedEmployeeProduct ? $updatedEmployeeProduct->pivot->stock : 0;

            return response()->json([
                'success' => true,
                'message' => 'Transfer stok berhasil',
                'master_stock' => $product->fresh()->stock,
                'employee_stock' => $newEmployeeStock,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
