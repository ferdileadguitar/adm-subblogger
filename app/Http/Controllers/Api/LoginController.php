<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;

class LoginController extends Controller
{

	public function __construct()
	{
		$this->middleware('login');
	}

	public function tryLogin(Request $request, ...$postLogin)
    {
        $credentials = array(
            'username' => $request->input('username'),
            'password' => $request->input('password')
        );
        dd( $credentials );
        // Load user and try to login
        if (FALSE === ($postLogin = User::getUser($credentials)))
        { abort(500, 'Invalid Username or Password!'); }

        // Auth::login($user, FALSE);
        // return $this->response();
        // $postLogin = collect($this->user)->map(function($item) use ($credentials){
        //     if( $item['username'] == $credentials['username'] OR $item['email'] == $credentials['username'] AND Hash::check($credentials['password'], $item['password']) )
        //         return $item;
        // }); 

        // // Override postLogin
        $postLogin = $postLogin->first();

        if ( !empty($postLogin) )
        {   
            // dd( $postLogin->first() );
            // $request->session()->put('admin:username', $postLogin['username']);
            $request->session()->put('admin:username', $postLogin->display_name);
            // $request->session()->put('admin:username', $postLogin->username);

            // return response()->json('oK');
        }

        if (FALSE === (!empty($postLogin->first)))
        { return response()->json(array('status' => 400, 'message' => 'Invalid Username or Password!'), 400); }

    }
}
