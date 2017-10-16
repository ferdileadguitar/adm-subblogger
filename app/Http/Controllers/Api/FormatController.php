<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Format;

class FormatController extends \App\Http\Controllers\ApiController
{

	public $take = 10;

	public function getFormat(){
		return $this->response(Format::getFiltered($this->request)->cleanPaginate($this->take));
	}
}
