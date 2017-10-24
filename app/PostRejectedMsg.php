<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostRejectedMsg extends Model
{
	public $table = 'posts_rejected_msg';
	protected $fillable = [];

	public function posts()
	{ return $this->belongsTo('App\Post'); }
}
