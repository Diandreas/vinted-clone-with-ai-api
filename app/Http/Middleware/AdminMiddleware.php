<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Check if user is admin by flag, role, or explicit permissions
        $user = $request->user();
        $isAdmin = ($user->is_admin ?? false) || (($user->role ?? null) === 'admin');
        $permissions = $user->permissions ?? [];
        if (is_string($permissions)) {
            $decoded = json_decode($permissions, true);
            $permissions = is_array($decoded) ? $decoded : [];
        }

        $hasAdminPermission = in_array('dashboard:view', $permissions, true)
            || in_array('users:manage', $permissions, true)
            || in_array('products:moderate', $permissions, true)
            || in_array('analytics:view', $permissions, true);

        if (!$isAdmin && !$hasAdminPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
        }

        return $next($request);
    }
}
