<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    /**
     * Tampilkan form setup toko untuk admin baru
     * GET /store/setup
     */
    public function setup()
    {
        // Pastikan hanya user yang belum punya store yang bisa akses
        if (Auth::user()->store_id !== null) {
            return redirect()->route('dashboard');
        }

        return view('store.setup');
    }

    /**
     * Proses penyimpanan data toko
     * POST /store/setup
     */
    public function store(Request $request)
    {
        // Jika sudah punya store, tolak
        if (Auth::user()->store_id !== null) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama toko tidak boleh kosong',
            'name.max' => 'Nama toko tidak boleh lebih dari 100 karakter',
            'phone.max' => 'Nomor telepon tidak boleh lebih dari 20 karakter',
        ]);

        // Buat store baru
        $store = Store::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
        ]);

        // Update user dengan store_id yang baru dibuat
        $user = Auth::user();
        $user->store_id = $store->id;
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Toko berhasil dibuat! Selamat datang di dashboard.');
    }
}
