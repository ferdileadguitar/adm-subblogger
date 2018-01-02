<?php

namespace App\Http\Middleware;

use Closure;
use Session;

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

        // dd( \Session::all() );

        if( !$request->session()->exists('admin:username') )
        {
            return response()->json(['status' => 403, 'msg' => 'You don\'t have permission!'], 403);
        }

        return $response;
    }
}
