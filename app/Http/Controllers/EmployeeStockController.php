<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
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

        // Ambil semua produk master beserta stok yang dimiliki oleh pegawai ini
        $products = Product::with(['category', 'users' => function($query) use ($employee) {
            $query->where('user_id', $employee->id);
        }])->orderBy('name')->paginate(15);

        return view('employees.stock', compact('employee', 'products'));
    }

    /**
     * Mentransfer stok dari master ke pegawai atau sebaliknya.
     */
    public function transfer(Request $request, User $employee)
    {
        if ($employee->role !== 'pegawai') {
            return response()->json(['success' => false, 'message' => 'Hanya dapat mengatur stok untuk pegawai.'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|integer|not_in:0',
            // amount > 0 berarti transfer dari master ke pegawai
            // amount < 0 berarti tarik stok dari pegawai ke master
        ]);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($validated['product_id']);
            $amount = $validated['amount'];

            // Cek stok yang ada di pegawai saat ini
            $employeeProduct = $employee->products()->where('product_id', $product->id)->first();
            $currentEmployeeStock = $employeeProduct ? $employeeProduct->pivot->stock : 0;

            if ($amount > 0) {
                // Transfer ke pegawai: pastikan master punya cukup stok
                if ($product->stock < $amount) {
                    throw new \Exception("Stok master tidak mencukupi. Sisa stok: {$product->stock}");
                }
                
                // Kurangi master
                $product->decrement('stock', $amount);
                
                // Tambah ke pegawai
                if ($employeeProduct) {
                    $employee->products()->updateExistingPivot($product->id, ['stock' => $currentEmployeeStock + $amount]);
                } else {
                    $employee->products()->attach($product->id, ['stock' => $amount]);
                }
            } else {
                // Tarik dari pegawai: pastikan pegawai punya cukup stok
                $pullAmount = abs($amount);
                if ($currentEmployeeStock < $pullAmount) {
                    throw new \Exception("Stok pegawai tidak mencukupi untuk ditarik. Sisa stok pegawai: {$currentEmployeeStock}");
                }

                // Tambah ke master
                $product->increment('stock', $pullAmount);

                // Kurangi dari pegawai
                $employee->products()->updateExistingPivot($product->id, ['stock' => $currentEmployeeStock - $pullAmount]);
            }

            DB::commit();

            // Fetch the updated employee stock to return it
            $updatedEmployeeProduct = $employee->products()->where('product_id', $product->id)->first();
            $newEmployeeStock = $updatedEmployeeProduct ? $updatedEmployeeProduct->pivot->stock : 0;

            return response()->json([
                'success' => true, 
                'message' => 'Transfer stok berhasil',
                'master_stock' => $product->fresh()->stock,
                'employee_stock' => $newEmployeeStock
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
