<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        // Cek apakah user sudah login
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Cek apakah role user sesuai
        $allowedRoles = array_map('trim', $roles);
        if (!in_array($request->user()->role, $allowedRoles, true)) {
            return response()->json(['message' => 'Forbidden, role tidak sesuai'], 403);
        }

        return $next($request);
    }
}