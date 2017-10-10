<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmbedLog extends Model
{
	public $timestamps = false;
	
	protected $table   = 'view_logs_embed';
	protected $fillable= ['user_id', 'post_id', 'share_id'];

	public function getAll(){
		$paginate = self::with('posts');

		$paginate = $paginate->selectRaw('DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m-%d %H:%i:%s") as created_on');

		$paginate = $paginate->paginate(10)->toArray();

		return $paginate;
	}

	public function posts()
	{ return $this->belongsTo('App\Posts'); }
}
