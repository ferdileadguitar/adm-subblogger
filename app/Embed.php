<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Embed extends Model
{
	protected $table   = 'post_embed';
	protected $guarded = [];

	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->belongsTo('App\Post'); }
}