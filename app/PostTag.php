<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

// Model 
use App\Tag;

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

    public function addPostTags($tags = array(), $postID) {
      $data = array();
      foreach ($tags as $row)
      {
          if (empty($row)) { continue; }

          // Is slug's database record exists? If no lets create
          $tagID = Tag::where('slug', '=', $row); // Holder
          if($tagID->count() == 0) 
              $tagID = Tag::create([
                              'title'      => $row, 
                              'slug'       => str_slug($row)
                            ])->toArray();
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

          // $this->destroyTagByPost($post->id, $tags); // removing tags event
      }

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