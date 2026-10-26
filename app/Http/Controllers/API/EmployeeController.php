<?php

namespace App\Http\Controllers\API;

use App\Events\StoreDataChanged;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Menampilkan daftar karyawan
     * GET /api/employees
     */
    public function index(Request $request)
    {
        try {
            $query = User::where('store_id', $request->user()->store_id)->orderBy('name');

            // Filter pencarian
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($filter) use ($search) {
                    $filter->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $employees = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Daftar karyawan berhasil diambil',
                'data' => $employees
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail satu karyawan
     * GET /api/employees/{id}
     */
    public function show(User $employee)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Detail karyawan berhasil diambil',
                'data' => $employee
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan karyawan baru
     * POST /api/employees
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'role_id' => [$request->user()->isOwner() ? 'nullable' : 'required', Rule::exists('roles', 'id')->where('store_id', $request->user()->store_id)],
            ]);

            $role = isset($validated['role_id'])
                ? Role::findOrFail($validated['role_id'])
                : Role::where('store_id', $request->user()->store_id)->where('is_system', true)->firstOrFail();
            if (! $request->user()->canAssignRole($role)) {
                return response()->json(['success' => false, 'message' => 'Role tersebut memiliki izin di luar kewenangan Anda.'], 403);
            }

            // Buat karyawan baru
            $employee = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'role' => 'pegawai',
                'role_id' => $role->id,
                'store_id' => $request->user()->store_id,
            ]);
            StoreDataChanged::dispatch($request->user()->store_id, ['employees']);

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil ditambahkan',
                'data' => $employee
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui karyawan
     * PUT /api/employees/{id}
     */
    public function update(Request $request, User $employee)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'email' => 'sometimes|email|unique:users,email,' . $employee->id,
                'role_id' => ['sometimes', Rule::exists('roles', 'id')->where('store_id', $request->user()->store_id)],
            ]);

            if ($employee->isOwner()) {
                return response()->json(['success' => false, 'message' => 'Akun pemilik tidak dapat diubah dari endpoint pegawai.'], 403);
            }
            if (isset($validated['role_id'])) {
                $role = Role::findOrFail($validated['role_id']);
                if (! $request->user()->canAssignRole($role)) {
                    return response()->json(['success' => false, 'message' => 'Role tersebut memiliki izin di luar kewenangan Anda.'], 403);
                }
                $validated['role'] = 'pegawai';
            }

            // Update karyawan
            $employee->update($validated);
            StoreDataChanged::dispatch($request->user()->store_id, ['employees']);

            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diperbarui',
                'data' => $employee
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus karyawan
     * DELETE /api/employees/{id}
     */
    public function destroy(User $employee)
    {
        try {
            if ($employee->isOwner()) {
                return response()->json(['success' => false, 'message' => 'Akun pemilik tidak dapat dihapus.'], 403);
            }
            $name = $employee->name;
            $photoPath = $employee->profile_photo_path;
            $storeId = $employee->store_id;
            $employee->delete();

            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            StoreDataChanged::dispatch($storeId, ['employees']);

            return response()->json([
                'success' => true,
                'message' => "Karyawan '$name' berhasil dihapus"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan data penjualan karyawan
     * GET /api/employees/{id}/sales
     */
    public function getSalesData(User $employee, Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now());

            $sales = Sale::where('user_id', $employee->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->with('saleItems.product')
                ->orderByDesc('transaction_date')
                ->get();

            $stats = [
                'total_sales' => $sales->sum('total'),
                'total_transactions' => $sales->count(),
                'average_transaction' => $sales->count() > 0 ? $sales->sum('total') / $sales->count() : 0,
                'total_items_sold' => $sales->sum(function ($sale) {
                    return $sale->saleItems->sum('quantity');
                }),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Data penjualan karyawan berhasil diambil',
                'data' => $sales,
                'stats' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
