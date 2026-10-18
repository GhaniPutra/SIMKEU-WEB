<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $permissions = collect($permissions)->flatMap(fn ($item) => explode(',', $item));
        $authorized = $permissions->contains(fn ($item) => $request->user()?->hasPermission($item));
        abort_unless($authorized, 403, 'Anda tidak memiliki izin untuk melakukan tindakan ini.');

        return $next($request);
    }
}
