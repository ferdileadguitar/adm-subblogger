<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;

class AuthorController extends \App\Http\Controllers\PageController
{

	public function index() 
	{
		return $this->view('authors', []);
	}
}
