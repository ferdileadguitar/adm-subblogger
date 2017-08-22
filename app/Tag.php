<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
	protected $guarded = [];
	public $table = 'tags';
    public $fillable = ['tag_id', 'post_id', 'title', 'slug', 'created_on', 'updated_on'];
	public $timestamps = false;
	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function postTags()
	{ return $this->hasMany('App\PostTag'); }

	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->hasMany('App\Post'); }
}