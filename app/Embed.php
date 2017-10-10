<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Embed extends Model
{
	public $timestamps = false;
	protected $table   = 'post_embed';
	protected $guarded = [];
	protected $fillable= ['id_post', 'accessurl', 'domain', 'shareid', 'view', 'created_on'];

	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->belongsTo('App\Post'); }
}