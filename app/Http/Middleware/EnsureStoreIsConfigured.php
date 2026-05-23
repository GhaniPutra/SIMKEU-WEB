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
        // Pastikan user sudah login, rolenya admin, dan store_id-nya null
        if (Auth::check() && Auth::user()->role === 'admin' && is_null(Auth::user()->store_id)) {
            // Cek jika request bukan ke route store setup itu sendiri agar tidak terjadi redirect loop
            if (!$request->routeIs('store.setup') && !$request->routeIs('store.store') && !$request->routeIs('logout')) {
                return redirect()->route('store.setup');
            }
        }

        return $next($request);
    }
}
