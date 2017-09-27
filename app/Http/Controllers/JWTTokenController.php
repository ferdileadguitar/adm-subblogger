<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Http\Request;

class JWTTokenController extends Controller
{

	public function getToken() {
		// $customClaims = ['fruit' => 'apple', 'herb' => 'basil'];
		// $payload      = JWTFactory::make($customClaims);
		$token = JWTAuth::getToken();
       	// $user = JWTAuth::toUser($token);
		// $token        = JWTAuth::getToken();

		return response()->json($token);
		// return redirect('http://someurl?token='.$token);
	}
}
