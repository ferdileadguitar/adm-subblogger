<?php

namespace App\Http\Controllers\Auth;

use Closure;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // use AuthenticatesUsers;

    protected $user;
    protected $redirectTo = '/';

    // ------------------------------------------------------------------------

    public function __construct(Request $request)
    {   
        $this->user  = config('login.admin');

        // Override Original Middleware
        $this->middleware('admin.promise');
    }

    // ------------------------------------------------------------------------

    public function showLoginForm() {
        return view('auth.login');
    }

    public function logout(Request $request)
    { 
        $request->session()->flush();

        return redirect()->route('login');
    }

    // ------------------------------------------------------------------------
    /**
      * Illuminate\Http\Request $request
      * @params String $postLogin
      * @return json 
    */
    public function tryLogin(Request $request, ...$postLogin)
    {
        $credentials = array(
            'username' => $request->input('username'),
            'password' => $request->input('password')
        );
        // dd( $credentials );
        // Load user and try to login
        $postLogin = collect($this->user)->map(function($item) use ($credentials){
            if( $item['username'] == $credentials['username'] OR $item['email'] == $credentials['username'] AND Hash::check($credentials['password'], $item['password']) )
                return $item;
        }); 

        // Override postLogin
        $postLogin = $postLogin->first();

        if ( !empty($postLogin) AND $request->ajax() )
        {   
            $request->session()->put('admin:username', $postLogin['username']);

            // return response()->json('oK');
        }

        if (FALSE === (!empty($postLogin->first)))
        { return response()->json(array('status' => 400, 'message' => 'Invalid Username or Password!'), 400); }

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
