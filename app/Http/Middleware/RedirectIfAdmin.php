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

        // $this->auth     = Auth();
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $response = $next($request);
        
        if( $request->session()->exists('admin:username') )
        {
            return redirect()->route('content');
        }

        return $response;
    }
}
