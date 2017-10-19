<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthenticated
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
        $response = $next($request);

        if( !$request->session()->exists('admin:username') )
        {
            return response()->json(['msg' => 'You don\'t have permission!'], 400);
        }

        return $response;
    }
}
