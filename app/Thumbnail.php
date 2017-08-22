<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Thumbnail extends Model
{
    // Change timestamps
    public $timestamps = false;

    public $fillable = ['object_file_id', 'size'];

    public function getThumbnails($id, $path, $filename){
      $thumbnails = $this->where('object_file_id','=',$id)->get();
      $data = [];
      foreach ($thumbnails as $key => $value) {
        $data[$key] = ['path'=> $path.'/'.$value->size.'/'.$filename, 'id'=> $value->id];
      }

      return $data;
    }

	// Relationship

	/**
	 * Belongs to ObjectFile
	 * @return object
	 */
    public function object_files()
    {
    	return $this->belongsTo('App\ObjectFile');
    }
}
