<?php

namespace App\Http\Controllers;

class ApiController extends Controller
{
    protected function response($data = [], $status = 200)
    {
    	return response()->json($data, $status);
    }
}
