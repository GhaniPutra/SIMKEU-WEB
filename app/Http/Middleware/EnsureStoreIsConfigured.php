<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreIsConfigured
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && is_null(Auth::user()->store_id)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Akun belum terhubung ke toko. Selesaikan setup toko terlebih dahulu.'], 403);
            }
            if (Auth::user()->role === 'admin' && ! $request->routeIs('store.setup') && ! $request->routeIs('store.store') && ! $request->routeIs('logout')) {
                return redirect()->route('store.setup');
            }
            if (Auth::user()->role !== 'admin' && ! $request->routeIs('logout')) {
                abort(403, 'Akun belum terhubung ke toko.');
            }
        }

        return $next($request);
    }
}
