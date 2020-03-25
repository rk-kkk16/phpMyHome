<?php

namespace App\Http\Middleware;

use Closure;

class HttpsProtocol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->secure() && config('app.env') === 'production') {
            if ($_SERVER["HTTP_X_FORWARDED_PROTO"] != 'https') {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}
