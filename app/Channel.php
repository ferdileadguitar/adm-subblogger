<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
	protected $guarded = [];

	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->hasMany('App\Post'); }
}