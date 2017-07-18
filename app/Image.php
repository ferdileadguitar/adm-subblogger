<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
	protected $table = 'object_files';
	protected $guarded = [];

	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->hasMany('App\Post'); }

	// ------------------------------------------------------------------------
	
	public function user()
	{ return $this->hasMany('App\User'); }
}