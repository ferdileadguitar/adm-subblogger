<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;

// Models
use App\Post;
use App\User;

class ContentController extends \App\Http\Controllers\PageController
{
    
    public function index()
    {
        return $this->view('content', ['moderationCount' => Post::countModerated()]);
    }

    public function getPost($username = FALSE)
    {   
        // Make sure user params has isset
        if( !$username )
        { return abort(404); }

        // Get user detail 
        $user = User::getUserDetail($username);

        // No user abort 404
        if( !empty($user) )
        {
            return $this->view('content-user', [
                        'user_id'  => $user->id,
                        'username' => $user->username
                ]);
        }else
        {
            abort(404);
        }

    }
}
