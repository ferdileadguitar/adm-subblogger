<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// Model 
use App\Tag;
use App\Post;

use App\Events\KeepoCache;

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
      $exists_tag = self::where(['post_id' => $post_id])->with('tag')->get(); // lets search tags within already exists on DB
      $response   = array();

      // dd( $tag_requests );
      foreach ($exists_tag as $t) {
        if(!in_array($t->tag->slug, $tag_requests)){
          $post_tag = self::where('tag_id',$t->tag_id);
          // dd( $t );
          if(!$post_tag->delete()) {
            return false;
          }
        }
      }
      return true;
    }

    public function addPostTags($tags = array(), $postID) {
      $data    = array();
      
      $post    = Post::where(['id' => $postID]);

      foreach ($tags as $row)
      {
          if (empty($row)) { continue; }

          // Is slug's database record exists? If no lets create
          $tagID = Tag::where('slug', '=', $row); // Holder
          if($tagID->count() == 0) 
              $tagID = Tag::create(['title' => $row, 'slug' => str_slug($row)])->toArray();
          else 
              $tagID = $tagID->select('id', 'title')->first()->toArray();

          // Mapping
          $data = collect($data)->push($tagID)->map(function($item){
                    $give['id']    = $item['id'];
                    $give['title'] = $item['title'];
                    return $give;
          })->all();

          // Is tag_id - post_id already exists on server? Anticipating edit, 
          if(PostTag::where('tag_id', '=', $tagID['id'])->where('post_id', '=', $postID)->count() == 0)
              PostTag::insert(['tag_id' => $tagID['id'], 'post_id' => $postID]);

          $this->destroyTagByPost($postID, $tags); // removing tags event
      }

      // Flush cache
      event(new KeepoCache($post));

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