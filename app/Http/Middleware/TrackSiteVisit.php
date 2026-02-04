<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackSiteVisit
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->isMethod('GET')) {
            return $response;
        }

        if ($request->expectsJson()) {
            return $response;
        }

        if ($request->is('storage/*') || $request->is('payment/*')) {
            return $response;
        }

        $ip = $request->ip();
        if ($ip) {
            $ipHash = hash_hmac('sha256', $ip, config('app.key'));
            $visitedOn = now()->toDateString();

            DB::table('site_visits')->updateOrInsert(
                [
                    'visited_on' => $visitedOn,
                    'ip_hash' => $ipHash,
                ],
                [
                    'user_id' => Auth::id(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'path' => substr($request->path(), 0, 255),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return $response;
    }
}
