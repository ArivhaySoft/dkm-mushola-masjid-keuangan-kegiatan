<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;

class TrackVisitor
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->header('X-Forwarded-For')
            ? explode(',', $request->header('X-Forwarded-For'))[0]
            : $request->ip();
        $ip = trim($ip);
        $today = now()->toDateString();

        Visitor::firstOrCreate(
            ['tanggal' => $today, 'ip' => $ip],
            ['user_agent' => $request->userAgent()],
        );

        return $next($request);
    }
}
