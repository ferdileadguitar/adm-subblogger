<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Author;

class AuthorsController extends \App\Http\Controllers\ApiController
{
	protected $users;
	protected $take = 10;

	public function getAuthors()
	{
		return $this->response(Author::getFiltered($this->request)->cleanPaginate($this->take));
	}

	public function setPassword()
	{
		$result = Author::getFiltered($this->request)->resetPassword($this->request->input('id'), $this->request->input('password'));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404)->send(); }

		return $this->response($result, 200)->send();
	}

	public function setUserStatus()
	{
		$result = Author::getFiltered($this->request)->setStatus(explode(',', $this->request->input('id')));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404)->send(); }

		return $this->response($result, 200)->send();
	}

	public function authorState($type)
	{
		switch ($type) {
			case 'set-password':
				$this->setPassword();
				break;
			case 'delete-users':
			$this->setUserStatus();
				break;
			default:
				return $this->response(array('status' => 'Failed', 'description' => 'Bad request'), 400);
				break;
		}
	}
}
