<?php

namespace App\Http\Middleware;

use Closure;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            $decodeToken = parseJwt($request->header('Authorization'));

            if ($decodeToken->user->role == env('ROLE_SPA')) {
                return $next($request);
            }

            return writeLog('Unauthorize');
        } catch (\Throwable $th) {
            return writeLog($th->getMessage());
        }
    }
}
