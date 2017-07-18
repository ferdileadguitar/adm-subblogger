<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
	protected $guarded = [];

	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function postTags()
	{ return $this->hasMany('App\PostTag'); }

	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->hasMany('App\Post'); }
}