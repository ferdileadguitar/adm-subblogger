<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChannelController extends \App\Http\Controllers\PageController
{

	public function index() {
		return $this->view('channel', []);
	}
}
