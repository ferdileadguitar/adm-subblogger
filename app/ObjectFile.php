<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ObjectFile extends Model
{
    // Change timestamps
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';

    public $fillable = ['file_name', 'file_type', 'file_path', 'full_path', 'raw_name', 'orig_name', 'client_name', 'file_ext', 'file_size', 'is_image', 'image_width', 'image_height', 'image_type', 'image_size_str'];

    public function getImagePath($id){
      return $this->where('id','=',$id);
    }

	// Relationship

    /**
     * Has many Post
     * @return object
     */
    public function posts()
    {
        return $this->hasMany('App\Post');
    }

	/**
	 * Has many Thumbnail
	 * @return object
	 */
    public function thumbnails()
    {
    	return $this->hasMany('App\Thumbnail');
    }
}
