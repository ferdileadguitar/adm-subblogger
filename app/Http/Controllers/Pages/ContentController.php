<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;

// Models
use App\Post;

class ContentController extends \App\Http\Controllers\PageController
{
	protected $globalData = [];

	// ------------------------------------------------------------------------
	
    public function __construct()
    {
        $this->globalData = [
    		'pageTitle' => 'Contents - ' . config('app.name'),
    		'activeNav' => 'contents'
    	];
    }

    // ------------------------------------------------------------------------
    
    public function index(Request $request)
    {
        return $this->view('content', array_merge($this->globalData, [
            'moderationCount' => Post::countModerated()
        ]));
    }
}
