<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        // Cek apakah user sudah login
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek apakah role user sesuai
        if ($request->user()->role !== $role) {
            return response()->json(['message' => 'Forbidden, role tidak sesuai'], 403);
        }

        return $next($request);
    }
}