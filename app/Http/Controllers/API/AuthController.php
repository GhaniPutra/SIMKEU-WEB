<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use App\Models\Role;
use App\Support\RolePermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register user baru
     * POST /api/register
     */
    public function register(Request $request)
    {
        try {
            // Validasi input dari request
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'store_name' => 'nullable|string|max:100',
            ], [
                'name.required' => 'Nama tidak boleh kosong',
                'name.max' => 'Nama tidak boleh lebih dari 100 karakter',
                'email.required' => 'Email tidak boleh kosong',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'password.required' => 'Password tidak boleh kosong',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak sesuai',
            ]);

            // Buat user baru
            $user = DB::transaction(function () use ($validated) {
                $store = Store::create([
                    'name' => $validated['store_name'] ?? 'Toko ' . $validated['name'],
                ]);

                Role::create([
                    'store_id' => $store->id,
                    'name' => 'Pegawai',
                    'permissions' => RolePermissions::starterEmployee(),
                    'is_system' => true,
                ]);

                return User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'store_id' => $store->id,
                    'role' => 'admin',
                ]);
            });

            // Generate API token menggunakan Sanctum
            $token = $user->createToken('api-token')->plainTextToken;

            // Return response sukses dengan user dan token
            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
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
     * Login user
     * POST /api/login
     */
    public function login(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'Email tidak boleh kosong',
                'email.email' => 'Format email tidak valid',
                'password.required' => 'Password tidak boleh kosong',
            ]);

            // Cari user berdasarkan email
            $user = User::where('email', $validated['email'])->first();

            // Cek apakah user ada dan password benar
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email atau password salah',
                ], 401);
            }

            // Generate API token menggunakan Sanctum
            $token = $user->createToken('api-token')->plainTextToken;

            // Return response sukses dengan user dan token
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $user,
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
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
     * Logout user (revoke token)
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        try {
            // Revoke token saat ini
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat logout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     * GET /api/user
     */
    public function currentUser(Request $request)
    {
        try {
            $user = $request->user()->loadMissing('accessRole');
            $role = $user->accessRole;
            $user->setAttribute('role_name', $user->role_name);
            $user->setAttribute('permissions', $user->isOwner()
                ? RolePermissions::all()
                : (($role && (int) $role->store_id === (int) $user->store_id)
                    ? ($role->permissions ?? [])
                    : ($user->role_id === null ? RolePermissions::legacyEmployee() : [])));

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     * POST /api/user/profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Validasi input
            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
            ]);

            // Update user
            $user->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $user
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
}
