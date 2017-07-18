<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

// Models
use App\User;
use App\Post;

class ContentController extends \App\Http\Controllers\ApiController
{
	protected $posts;
	protected $take = 10;

	// ------------------------------------------------------------------------
	
	public function getContents(Request $request)
	{
		return $this->response(Post::getFiltered($request)->cleanPaginate($this->take));
	}

	// ------------------------------------------------------------------------
	
	public function deleteContent(Request $request)
	{
		$result = Post::updateStatus(explode(',', $request->input('id')), -99);

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response([
			'moderationCount' => $result['moderationCount']
		], 200);
	}

	// ------------------------------------------------------------------------
	
	public function getCountModerated()
	{
		return $this->response(['counted' => Post::countModerated()]);
	}

	// ------------------------------------------------------------------------
	
	public function setStatus(Request $request)
	{
		$result = Post::updateStatus(explode(',', $request->input('id')), $request->input('status'));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response([
			'moderationCount' => $result['moderationCount']
		], 200);
	}

	// ------------------------------------------------------------------------
	
	public function setSticky(Request $request) 
	{
		$result = Post::updateStickyPremium(explode(',', $request->input('id')), 'sticky', $request->input('set'));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response('ok');
	}

	// ------------------------------------------------------------------------
	
	public function setPremium(Request $request) 
	{
		$result = Post::updateStickyPremium(explode(',', $request->input('id')), 'premium', $request->input('set'));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response('ok');
	}
}
