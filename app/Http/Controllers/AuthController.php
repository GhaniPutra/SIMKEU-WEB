<?php

namespace App\Http\Controllers;

// ====================================
// Import Model untuk mengakses database
// ====================================
use App\Models\User;

// ====================================
// Import untuk autentikasi dan validasi
// ====================================
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ====================================
    // Method untuk menampilkan halaman login
    // ====================================
    /**
     * Tampilkan form login
     * GET /login
     */
    public function showLoginForm()
    {
        // Jika user sudah login, arahkan ke halaman pertama yang diizinkan.
        if (Auth::check()) {
            return redirect()->route('home');
        }

        // Tampilkan view login.blade.php
        return view('auth.login');
    }

    // ====================================
    // Method untuk proses login
    // ====================================
    /**
     * Proses login user
     * POST /login
     */
    public function login(Request $request)
    {
        // ====================================
        // Validasi input dari user
        // ====================================
        $validated = $request->validate([
            'email' => 'required|email',     // Email harus ada dan format email yang valid
            'password' => 'required|string', // Password harus ada dan bertipe string
        ], [
            // Pesan error dalam bahasa Indonesia
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password tidak boleh kosong',
        ]);

        // ====================================
        // Coba melakukan autentikasi dengan email dan password
        // ====================================
        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            // Jika login berhasil, regenerate session untuk keamanan
            $request->session()->regenerate();

            return redirect()->route('home')->with('success', 'Login berhasil');
        }

        // ====================================
        // Jika login gagal, kembali ke form login dengan pesan error
        // ====================================
        return back()->withInput($request->only('email'))
                     ->withErrors(['email' => 'Email atau password salah']);
    }

    // ====================================
    // Method untuk menampilkan halaman register
    // ====================================
    /**
     * Tampilkan form register
     * GET /register
     */
    public function showRegisterForm()
    {
        // Jika user sudah login, arahkan ke halaman pertama yang diizinkan.
        if (Auth::check()) {
            return redirect()->route('home');
        }

        // Tidak perlu ambil toko lagi karena semua user adalah admin utama
        return view('auth.register');
    }

    // ====================================
    // Method untuk proses register
    // ====================================
    /**
     * Proses registrasi user baru
     * POST /register
     */
    public function register(Request $request)
    {
        // ====================================
        // Validasi input dari form register
        // ====================================
        $validated = $request->validate([
            'name' => 'required|string|max:100',           // Nama harus ada
            'email' => 'required|email|unique:users',       // Email harus unik di tabel users
            'password' => 'required|string|min:6|confirmed', // Password minimal 6 karakter dan harus konfirmasi
            // semua user adalah admin utama sehingga tidak menyimpan store_id
        ], [
            // Pesan error dalam bahasa Indonesia
            'name.required' => 'Nama tidak boleh kosong',
            'name.max' => 'Nama tidak boleh lebih dari 100 karakter',
            'email.required' => 'Email tidak boleh kosong',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password tidak boleh kosong',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
        ]);

        // ====================================
        // Buat user baru di database
        // ====================================
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Enkripsi password
            'store_id' => null, // semua user adalah admin utama
            'role' => 'admin', // semua login adalah admin
        ]);

        // ====================================
        // Login user secara otomatis setelah register
        // ====================================
        Auth::login($user);

        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        // Redirect ke dashboard dengan pesan sukses
        return redirect()->route('dashboard')->with('success', 'Registrasi berhasil, selamat datang ' . $user->name);
    }

    // ====================================
    // Method untuk logout
    // ====================================
    /**
     * Logout user
     * POST /logout
     */
    public function logout(Request $request)
    {
        // Logout user dari session
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token untuk keamanan
        $request->session()->regenerateToken();

        // Redirect ke halaman utama setelah logout
        return redirect()->route('home')->with('success', 'Anda telah logout');
    }
}
