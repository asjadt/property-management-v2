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
        if (!session('dev_access_granted')) {
            return redirect()->route('dev.login');
        }

        return $next($request);
    }
}
