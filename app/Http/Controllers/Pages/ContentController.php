<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;

// Models
use App\Post;
use App\User;

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

    public function getPost(Request $request, $username = FALSE)
    {   
        // Make sure user params has isset
        if( !$username )
        { return abort(404); }

        // Get user detail 
        $user = User::getUserDetail($username);

        // No user abort 404
        if( !empty($user) )
        {
            return $this->view('content-user', array_merge($this->globalData, [
                        'user_id'  => $user->id,
                        'username' => $user->username
                ]));
        }else
        {
            abort(404);
        }

    }
}
