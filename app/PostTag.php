<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostTag extends Model
{
	public $table       = 'posts_tags';
    public $timestamps  = false;
    public $fillable    = ['tag_id','post_id'];

    // destroy tags on post
    /**
    * @param integer $post_id
    * @param array $tag_request (array tags on input request)
    */
    public function destroyTagByPost($post_id, $tag_requests){
      $exists_tag = (new Post)->getTagsByPost($post_id); // lets search tags within already exists on DB
      foreach ($exists_tag as $t) {
        if(!in_array($t->slug, $tag_requests)){
          $post_tag = (new PostTag)->where('tag_id',$t->id);
          if(!$post_tag->delete()){
            return false;
          }
        }
      }

      return true;
    }

    public function getPostByTags($tags=[])
    {
      $cacheKey = implode('/',$tags);
      $cacheTags = ['mptg:'.implode('/',$tags)]; // mpl = model post latest
      $data = RubberCache::buildCache($cacheKey, $cacheTags, 10, $tags, function($tags){
        $tagTName = with((new Tag)->getTable()); // Tag tablename
        $thisTName    = $this->getTable(); // This tablename
        $posts = $this->join($tagTName, $thisTName.'.tag_id','=',$tagTName.'.id')
                      ->whereIn($tagTName.'.slug',$tags)
                      ->groupBy($thisTName.'.post_id')
                      ->orderBy($thisTName.'.post_id','desc')
                      ->get();
        return $posts;
      });

      return $data;
    }


    // Belong to Relationship

    /**
     * Belong to Tag
     * @return object
     */
    public function tag()
    {
        return $this->belongsTo('App\Tag','tag_id','id');
    }

    /**
     * Belong to Post
     * @return object
     */
    public function post()
    {
        return $this->belongsTo('App\Post','post_id','id');
    }
}