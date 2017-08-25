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

    public function setPostTags($tags = array(), $post_id, $response = false) {
        // Createad array after implode
        $tags   = explode(config('feeds.tag_separator'), $this->request->input('tags'));

        $result = (new PostTag)->addPostTags($tags, $post_id);

        if( empty($result) )
        { return $this->abortRequest(400, 'bad_request', 'We\'ve failed to store your tags :()'); }

        // Sorry, it's look not advanced
        $result = (!$response) ? $result : array('tags' => $result);

        // Hmm um hahh hufffttttt,  No way ... :(
        return $result;
    }

    public function setPostTitle() {
        $result = Post::updatePostTitle( $this->request['id'], $this->request['title'] );

        if ( isset($result['error']))
        { return $this->response($result['error'], 404); }

        return response()->json($result)->send();
    }

    public function setPostChannel() {
        $result = Post::updatePostChannel( $this->request['id'], $this->request['channel.slug'] );

        if ( isset($result['error']))
        { return $this->response($result['error'], 404); }

        return response()->json($result)->send();
    }

    public function setPostCreated() {
        $result = Post::updatePostCreated( $this->request['id'], $this->request['created'] );

        if ( isset($result['error']) )
        { return $this->response($result['error'], 404); }

        return response()->json($result)->send();
    }

    public function putFeed($type = null) {
        switch ($type) {
            case 'set-title':
                $this->setPostTitle();
                break;
            case 'set-tags':
                $tags   = $this->request->input('tags');

                $result = $this->setPostTags($tags, $this->request->input('id'), TRUE);

                return response()->json($result);
                break;
            case 'set-channel':
                $this->setPostChannel();
                break;
            case 'set-date':
                $this->setPostDate();
                break;
            case 'set-created':
                $this->setPostCreated();
                break;
            default:
                $this->setPostFeeds();
                break;
        }
    }

    public function setPostFeeds() {
        if(empty($this->request->input('title')))
            return $this->abortRequest(404, 'not_found', 'You must at least fill Title and Category')->send();

        // Get Post ID
        $postID = @Post::where('slug', '=', $this->request->input('slug'))->first();

        $tags = explode(config('feeds.tag_separator'), $this->request->input('tags'));

        // Lets search which is exists or not and also append

        // Is this feed exists?
        if($postID == null)
            return $this->abortRequest(400, 'bad_request', 'Issue is not exists')->send();

        $data = array(
            'object_file_id' => @ObjectFile::find($this->request->input('image.id'))->id, // Is Image/Object ID exists?
            // 'slug'           => str_slug($this->request->input('title'), '-'),
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
            return $this->abortRequest(400, 'bad_request', 'Wrong content format (1)')->send();

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
                                return $this->abortRequest(400, 'bad_request', 'Wrong content format (1)')->send();
                        }
                    }
                    else
                        return $this->abortRequest(400, 'bad_request', 'Wrong content format (2)')->send();
                break;
        	default:
        		return $this->abortRequest(404, 'bad_request', 'Sorry your content type is not allowed in here :(')->send();
        		break;
        }

        if( in_array($data['post_type'], ['listicle']) )
            $data['content']   = json_encode($JSONContent);
    

        // Validation some options
        if( is_null($data['channel_id']) )
        	$this->abortRequest(404, 'bad_request', 'Please choose the category')->send();

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
            
            // Update Tags
            $tags = $this->setPostTags($tags, $postID->id);

            // Now update the feeds
            $post = (new Post)->updatePostFeeds($data, $postID);

            // if( !empty($post) ) {
                // $post = $post;
            // }

        	// Let's give the response
        	return response()->json($post)->send(); 
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
