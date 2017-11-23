<?php

namespace App\Http\Middleware;

use Closure;

class LoginAuthenticated
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

        if( $request->session()->exists('admin:username') )
        {
            return redirect()->route('content');
        }
        
        // Ajax requesy
        if( $request->ajax() )
        {
            return response()->json(['status' => 403, 'msg' => 'You don\'t have permission!'], 403);
        }

        return $response;
    }
}
