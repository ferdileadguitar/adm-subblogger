<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\LoginController as Auth;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Closure;

class RedirectIfAdmin
{
    private $user;
    protected $request;
    protected $auth;

    public function __construct () {
        $this->user     = config('login.admin');
        
        $this->request  = Request();

        $this->auth     = Auth();
    }
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

        if( $request->session()->exists('_username') )
        {
            if ( $request->ajax() ) 
            {
                return response()->json(['status' => 'oK', 'url' => url('/')], 200);
            }

            return redirect()->route('content');
        }

        return $response;
    }
}
