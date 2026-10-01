<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsKoordinatorOrAbove
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAtLeastKoordinator()) {
            abort(403, 'Halaman ini hanya untuk Koordinator atau SuperAdmin.');
        }

        return $next($request);
    }
}
