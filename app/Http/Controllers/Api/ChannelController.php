<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Channel;

class ChannelController extends \App\Http\Controllers\ApiController
{

	public $take = 10;

	public function getChannel(){
		return $this->response(Channel::getFiltered($this->request)->cleanPaginate($this->take));
	}
}
