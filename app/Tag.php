<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
	const CREATED_AT   = 'created_on';
	const UPDATED_AT   = 'updated_on';
	protected $guarded = [];
	public $table      = 'tags';
    public $fillable   = ['title', 'slug'];
	// public $timestamps = false;
	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function postTags()
	{ return $this->hasMany('App\PostTag'); }

	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->hasMany('App\Post'); }
}