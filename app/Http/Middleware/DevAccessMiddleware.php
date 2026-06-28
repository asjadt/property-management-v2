<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DevAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!env("DEVELOPER_LOGIN_ENABLED", true)) {
            return response()->json([
                "message" => "developer login is not enabled."
            ], 403);
        }

        if (!$request->session()->get('developer_authenticated') && !$request->session()->get('dev_access_granted') && !env("DeveloperAutoLogin", false)) {
            return redirect()->route('dev.login');
        }

        return $next($request);
    }
}
