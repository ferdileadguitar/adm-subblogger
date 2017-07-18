<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Models
use App\User;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    // ------------------------------------------------------------------------

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // ------------------------------------------------------------------------
    
    public function logout()
    { 
        Auth::logout(FALSE);
        return redirect()->route('login');
    }

    // ------------------------------------------------------------------------

    public function tryLogin(Request $request)
    {
        $credentials = array(
            'username' => $request->input('username'),
            'password' => $request->input('password')
        );

        // Get user and try to login
        if (FALSE === ($user = User::getUser($credentials)))
        { abort(500, 'Invalid Username or Password!'); }

        // ------------------------------------------------------------------------
        
        Auth::login($user, FALSE);
        return $this->response();
    }

    // ------------------------------------------------------------------------
    
    private function response()
    {
        if (\Request::ajax())
        { 
            return response()->json([
                'status' => 'Welcome aboard, mate!',
                'url'    => url('/')
            ]);
        }

        return redirect()->route('content');
    }
}
