<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Author;

class AuthorsController extends \App\Http\Controllers\PageController
{
	protected $users;
	protected $take = 10;

	public function getAuthors()
	{
		return Author::getFiltered($this->request)->cleanPaginate($this->take);
	}
}
