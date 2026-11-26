<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Batasi endpoint yang hanya boleh diakses admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak. Endpoint ini khusus admin.',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
