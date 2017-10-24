<?php

namespace App\Http\Controllers\Auth;

use Closure;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use App\User;

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
        $this->middleware('login');

        // dd( $request->session() );
        
    }

    // ------------------------------------------------------------------------

    public function showLoginForm() {
        return view('auth.login');
    }

    public function logout(Request $request)
    { 
        $request->session()->flush('admin:username');
        // dd( $request->session() );
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

        // Load user and try to login
        if (FALSE === ($postLogin = User::getUser($credentials)))
        { abort(500, 'Invalid Username or Password!'); }

        // Auth::login($user, FALSE);

        // // Override postLogin
        if ( !empty($postLogin) )
        {   
            $msg = array( 'username' => $postLogin->username, 'display_name' => $postLogin->display_name, 'email' => $postLogin->email, 'ip' => $request->ip(), 'user-agent' => $request->header('User-Agent'));
            $request->session()->put('admin:username', $msg);

            Log::useFiles(base_path() . '/storage/logs/login/debug.log', 'info');
            Log::info(json_encode($msg));
        }

        if (FALSE === (!empty($postLogin)))
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
