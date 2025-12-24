<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UnderDevelopment
{
    public function handle(Request $request, Closure $next)
    {
        // contoh kondisi
        if (config('app.under_development') === true) {

            // biar tidak infinite loop
            if (!$request->is('under-development')) {
                return redirect()->route('under.development');
            }
        }

        return $next($request);
    }
}
