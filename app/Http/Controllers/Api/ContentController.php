<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

// Models
use App\User;
use App\Post;
use App\Tag;
use App\PostTag;
use App\ObjectFile;
use App\Channel;

class ContentController extends \App\Http\Controllers\ApiController
{
	protected $posts;
	protected $take = 10;

	// ------------------------------------------------------------------------
	
	public function getContents()
	{
		return $this->response(Post::getFiltered($this->request)->cleanPaginate($this->take));
	}

	// ------------------------------------------------------------------------
	
	public function deleteContent()
	{
		$result = Post::updateStatus(explode(',', $this->request->input('id')), -99);

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response([
			'moderationCount' => $result['moderationCount']
		], 200);
	}

	// ------------------------------------------------------------------------
	
	public function getCountModerated()
	{
		return $this->response(['counted' => Post::countModerated()]);
	}

	// ------------------------------------------------------------------------
	
	public function setStatus()
	{
		$result = Post::updateStatus(explode(',', $this->request->input('id')), $this->request->input('status'));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response([
			'moderationCount' => $result['moderationCount']
		], 200);
	}

	// ------------------------------------------------------------------------
	
	public function setSticky() 
	{
		$result = Post::updateStickyPremium(explode(',', $this->request->input('id')), 'sticky', $this->request->input('set'));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response('ok');
	}

	// ------------------------------------------------------------------------
	
	public function setPremium() 
	{
		$result = Post::updateStickyPremium(explode(',', $this->request->input('id')), 'premium', $this->request->input('set'));

		if (! empty($result['error']))
		{ return $this->response($result['error'], 404); }

		return $this->response('ok');
	}

	 /**
     * Get Tags
     * @return object
     */
    public function getTags()
    {
        $tags  = @Tag::where('slug', 'like', $this->request->input('q') . '%')->take(10)->get();

        return $this->giveJson($tags->pluck('slug')->toArray());
    }

    public function putFeed() {
    	$this->processFeed('edit');
    }

    public function processFeed($reqType) {
    	if(empty($this->request->input('title')))
            return $this->abortRequest(404, 'not_found', 'You must at least fill Title and Category');

        // Get Post ID
        $postID = @Post::where('slug', '=', $this->request->input('slug'))->first();

        // Is this feed exists?
        if($postID == null)
            return $this->abortRequest(400, 'bad_request', 'Issue is not exists');

        $data = array(
            'object_file_id' => @ObjectFile::find($this->request->input('image.id'))->id, // Is Image/Object ID exists?
            'slug'           => str_slug($this->request->input('title'), '-'),
            'title'          => $this->request->input('title'),
            'lead'           => $this->request->input('lead'),
            'excerpt'        => strip_tags($this->request->input('lead')),
            'source'         => preg_replace('/^http:\/\/(https?)/', '$1', $this->request->input('source')),
            'channel_id'     => @Channel::where('slug', '=', $this->request->input('channel.slug'))->first()->id,
            'post_type'      => $this->request->input('post_type'),
        );
        // Clean title and lead
        // ------------------------------------------------------------------------

        $data['title'] = preg_replace('~<br\s?\/?>$~ixu', '', $data['title']);
        $data['lead']  = ! empty($data['lead']) ? preg_replace('~<br\s?\/?>$~ixu', '', $data['lead']) : '';

        // Removing blank space at the of context
        $data['title'] = preg_replace('~(\&nbsp\;|\&amp\;nbsp\;)+$~', '', $data['title'] );
        $data['title'] = htmlentities($data['title'], ENT_QUOTES, 'UTF-8');

        // ------------------------------------------------------------------------

        // If object file id is empty, lets use default from config
        if($data['object_file_id'] == null)
            $data['object_file_id'] = config('feeds.default_object_file_id');

        // First, lets check content's JSON
        $JSONContent = ($data['post_type'] != 'article') ? json_decode($this->request->input('content'), true) : $this->request->input('content');
        if(empty($JSONContent)) // WHether its empty if JSON
            return $this->abortRequest(400, 'bad_request', 'Wrong content format (1)');

        switch ($data['post_type']) {
        	case 'article':
        		$data['content'] = $JSONContent; 
        		break;
        	case 'listicle':
        		if(isset($JSONContent['content'], $JSONContent['sort'], $JSONContent['models'])) // Cannot be null
                    {
                        // At least one models is done right
                        foreach ($JSONContent['models'] as $key => $row)
                        {
                            if(!array_keys_exists(array('order', 'title', 'image_str', 'content'), $row))
                                return $this->abortRequest(400, 'bad_request', 'Wrong content format (2)');
                        }
                    }
                    else
                        return $this->abortRequest(400, 'bad_request', 'Wrong content format (2)');
        	default:
        		$this->abortRequest(404, 'bad_request', 'Sorry your content type is not allowed in here :(');
        		break;
        }

        if( in_array($data['post_type'], ['listicle']) )
            $data['content']   = json_encode($JSONContent);

        // Validation some options
        if( is_null($data['channel_id']) )
        	$this->abortRequest(404, 'bad_request', 'Please choose the category');
        else {
        	if( $postID->title != $data['title'] )
        		$this->slugExistsCheck($data['slug']);
        	else
        	{
        		$tmpSlug = $postID->slug;
        		// unset($data['slug']);
        	}
        	$tmpSlug = $postID->slug;
        	$data = array_merge(['updated_on' => date('Y-m-d H:i:s')], $data);

        	// Now update the feeds
        	$post = Post::where([ 'id' => $postID->id ])->update($data);

            // if( !$post ) {
                unset($data['channel_id']);
            	$data = array_merge(
                        [
                            'id'      => $this->request->input('id'),
                            'channel' => array( 
                                        'slug'  => str_slug($this->request->input('channel.slug')), 
                                        'name'  => html_entity_decode($this->request->input('channel.name'))
                                    )
                        ], $data);
            // }
        	// 	$tags = $this->request->input('tags');

         //    	// dd($postID);
        	// 	if(! empty($tags))
         //        {
         //            // Lets search which is exists or not
         //            foreach ($tags as $row)
         //            {
         //                if (empty($row)) { continue; }

         //                // Is slug's database record exists? If no lets create
         //                $tagID = Tag::where('slug', '=', $row); // Holder
         //                if($tagID->count() == 0)
         //                    $tagID = Tag::create(['created_on'=> date('Y-m-d H:i:s'), 'updated_on'=> date('Y-m-d H:i:s'),'title' => $row, 'slug' => str_slug($row)])->id;
         //                else
         //                    $tagID = $tagID->first()->id;

         //                // Is tag_id - post_id already exists on server? Anticipating edit
         //                if(PostTag::where('tag_id', '=', $tagID)->where('post_id', '=', $postID)->count() == 0)
         //                    PostTag::insert(['tag_id' => $tagID, 'post_id' => $postID->id]);
         //            }
         //            // $check_removing_tags = (new PostTag)->destroyTagByPost($postID,$tags); // removing tags event
                    // }
        	// Let's give the response
        	return response()->json($data)->send(); 
        }
    }

    /**
     * Check if slug is exists, and make it unique for one more time
     * @param string &$slug
     * @return void
     */
    private function slugExistsCheck(&$slug)
    {
        if(Post::where('slug', '=', $slug)->count() > 0)
        {
            // Which one is available?
            for ($i=2; $i <= 1000; $i++)
            {
                if(Post::where('slug', '=', $slug.$i)->count() == 0)
                {
                    $slug .= $i;
                    break;
                }
            }
        }
    }
}
