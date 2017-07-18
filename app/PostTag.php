<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostTag extends Model
{
	protected $table = 'posts_tags';
	protected $guarded = [];

	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function tag()
	{ return $this->belongsTo('App\Tag'); }

	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->belongsTo('App\Post'); }
}