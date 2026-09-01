<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->esAdmin()) {
            abort(403, 'Esta acción requiere permisos de administrador.');
        }

        return $next($request);
    }
}
