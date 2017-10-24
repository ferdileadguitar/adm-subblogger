<?php

namespace App\Http\Controllers;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTFactory;
use Illuminate\Http\Request;

class JWTTokenController extends Controller
{

	public function getToken() {
		$token = JWTAuth::getToken();

		return response()->json($token);
	}
}
