<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Channel;
use App\objectFile;
use Carbon;

class Post extends Model
{
	public $timestamps = false;
	protected $guarded = [];

	private static $__instance = null;
	private static $postData = false;

	// ------------------------------------------------------------------------
	// Public Methods
	// ------------------------------------------------------------------------

	public static function getInstance()
	{
		if (self::$__instance === null)
		{ self::$__instance = new self; }

		return self::$__instance;
	}

	// ------------------------------------------------------------------------
	
	public static function getFiltered($request = FALSE)
	{
		// Init
		self::getInstance();
		self::$postData = self::with('user', 'image', 'channel', 'tag', 'share', 'embed');

		// ------------------------------------------------------------------------
		
		// Contributor only
		if ($request->input('contributor'))
		{ self::setContributorOnly(); }

		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }

		// Status
		if ($status = $request->input('status'))
		{ self::setStatus($status); }

		// Sort
		if ($sort = $request->input('key'))
		{ self::setSort($sort, $request->input('reverse')); }

		// Search
		if ($search = $request->input('search'))
		{ self::setSearch($search); }

		return self::$__instance;
	}

	// ------------------------------------------------------------------------
	
	public static function cleanPaginate($take = 50)
	{
		$paginate         = self::$postData->paginate($take)->toArray();
		$paginate['data'] = collect($paginate['data'])->map(function($post) {
			return [
				// Post
				'id'         => $post['id'],
				'title'      => html_entity_decode($post['title'], ENT_QUOTES),
				'lead'       => html_entity_decode($post['lead'], ENT_QUOTES),
				'slug'       => str_slug($post['slug']),
				'url'        => implode([config('app.keepo_url'), @$post['user']['username'], $post['slug']], '/'),
				// 'url'        => implode([config('app.url'), @$post['user']['username'], $post['slug']], '/'),
				'image'      => array(
								'id' 	=>  @$post['image']['id'],
								'url' 	=>  preg_replace('/https?\:/', '', @$post['image']['full_path']),
								'name' 	=>  @$post['image']['file_name']
							),
				'channel'    => array(
								'slug'	=> str_slug(@$post['channel']['slug']),
								'name' 	=> html_entity_decode(@$post['channel']['title'])
							),
				'post_type'  => $post['post_type'],
				'status'     => $post['status'],
				'views'      => $post['views'],
				'shares'     => @$post['share']['shares'],
				'embeds'     => count(@$post['embed']),
				'created'    => date('d M Y H:i', strtotime($post['created_on'])),

				//'reason'	 => 'Asd',
				'content'   => json_encode($post['content']),

				'is_sticky'  => $post['sticky'],
				'is_premium' => $post['premium'],
				
				// User
				'user'       => array(
					'id'			=> $post['user']['id'],
					'display_name' 	=> @$post['user']['display_name'],
					'url'   		=> $post['user'] ? url(implode(['users', $post['user']['username']], '/')) : null,
					'slug'			=> @$post['user']['slug']
				),	
				'source' 	=> $post['source'],
				// Tags
				'tags'       => collect(@$post['tag'])->map(function($tag) {
					return [
						'id'    => $tag['id'],
						'title' => $tag['title']
					];
				})
			];
		});

		return $paginate;
	}

	// ------------------------------------------------------------------------
	
	public static function countModerated()
	{
		return self::where('status', '-2')->count();
	}

	// ------------------------------------------------------------------------
	
	public static function updateStatus($postID = FALSE, $status = 0)
	{
		if (empty($postID)) { return ['error' => 'Post not found']; }

		// ------------------------------------------------------------------------
		
		if (is_array($postID)) { $post = self::whereIn('id', $postID); }
		else { $post = self::where('id', $postID); }

		$post->update(['status' => $status]);

		return ['moderationCount' => self::countModerated()];
	}

	// ------------------------------------------------------------------------
	
	public static function updateStickyPremium($postID = FALSE, $stickyOrPremium = 'sticky', $set = FALSE)
	{
		if (empty($postID)) { return ['error' => 'Post not found']; }

		// ------------------------------------------------------------------------
		
		if (is_array($postID)) { $post = self::whereIn('id', $postID); }
		else { $post = self::where('id', $postID); }

		$post->update([$stickyOrPremium => $set]);
	}

	public static function updatePostTitle($postID = FALSE, $postTitle = FALSE) {
		if( empty($postTitle) ) { return ['error' => 'Post not found']; }

		$post = self::where(['id' => $postID]);
		$post = $post->update(['title' => $postTitle]);
		return ['title' => $postTitle];
	} 

	public static function updatePostChannel($postID = false, $postChannel = FALSE) {
		if ( empty($postID) ) { return ['error' => 'Post not found']; }

		$post = self::with('channel')->where(function($query) use ($postChannel, $postID) {
			$channelID = Channel::where([ 'slug' => $postChannel ]);

			$query->where(['id' => $postID]);
			$query->update([ 'channel_id' => $channelID->first()->id ]);

			return $query;
		})->first();	

		return ['channel' => array( 'name' => html_entity_decode($post['channel']['title']), 'slug' => $post['channel']['slug'] ) ];
	}

	public static function updatePostImageCover($postID = false, $postImage = array()) {
		$objectFile  = array();

		if ( empty($postID) ) { return ['error' => 'Post not found']; }
		

		// Is image exist
		$objectFile = objectFile::where(['id' => $postImage['id']])->first();

		$post  = self::with('image')->where(function($query) use ($postID, $postImage, $objectFile) {

			$query->where(['id' => $postID]);
			$query->update(['object_file_id' => $objectFile->id]);

			return $query;
		})->first();

		// Should s not empty
		if ( !empty($post) AND !empty($objectFile) )
			$response = ['image' => array(
								'id' 	=> $objectFile['id'], 
								'name' 	=> $objectFile['file_name'], 
								'url'	=> preg_replace('/https?\:/', '', @$objectFile['full_path'])
							)
						];
		else
			return ['error' => 'Failed to post'];
		
		// Return data
		return $response;		
	}

	public static function updatePostCreated($postID = FALSE, $postCreated = FALSE) {
		if ( empty($postID) ) { return ['error' => 'Post not found']; }
		$convDate = date('Y-m-d', strtotime($postCreated)).' '.date('H:i:s');

		$post     = self::where(function($query) use ($postID, $postCreated, $convDate) {
			$query->where(['id' => $postID])->update(['created_on' => $convDate]);
			return $query;
		})->first();
		
		$post = date('d M Y H:i:s', strtotime($post->created_on));
		
		return ['created' => $post];
	}

	public static function updatePostFeeds($post = array(), $postID = FALSE, $response = array()) {
		if ( empty($postID) ) { return ['error' => 'Post no found']; }

		$post = self::with('user', 'image', 'channel', 'tag', 'share', 'embed')->where(function($query) use ($post, $postID) {
					$query->where(['id' => $postID->id])->update($post);
					return $query;
				});

		$post = $post->first()->toArray();

		$response = collect($response)->push($post)->map(function($items) {
			return [
				'id'         => $items['id'],
				'title'      => html_entity_decode($items['title'], ENT_QUOTES),
				'lead'       => html_entity_decode($items['lead'], ENT_QUOTES),
				'slug'       => str_slug($items['slug']),
				'url'        => implode(['https://keepo.me', @$items['user']['username'], $items['slug']], '/'),
				'image'      => array(
								'id' 	=>  @$items['image']['id'],
								'url' 	=>  preg_replace('/https?\:/', '', @$items['image']['full_path']),
								'name' 	=>  @$items['image']['fill_name']
							),
				'channel'    => array(
								'slug'	=> str_slug(@$items['channel']['slug']),
								'name' 	=> html_entity_decode(@$items['channel']['title'])
							),
				'post_type'  => $items['post_type'],
				'status'     => $items['status'],
				'views'      => $items['views'],
				'shares'     => @$items['share']['shares'],
				'embeds'     => count(@$items['embed']),
				'created'    => date('d M Y H:i', strtotime($items['created_on'])),

				//'reason'	 => 'Asd',
				'content'    => $items['content'],

				'is_sticky'  => $items['sticky'],
				'is_premium' => $items['premium'],
				
				// User
				'user'       => array(
					'id'			=> $items['user']['id'],
					'display_name' 	=> @$items['user']['display_name'],
					'url'   		=> $items['user'] ? url(implode(['users', $items['user']['username']], '/')) : null,
				),	
				'source' 	=> $items['source'],
				
				// Tags
				'tags'       => collect(@$items['tag'])->map(function($tag) {
					return [
						'id'    => $tag['id'],
						'title' => $tag['title']
					];
				})
			];
		});

		return $response->first();
	}

	// ------------------------------------------------------------------------
	// Private Methods
	// ------------------------------------------------------------------------
	
	private static function setContributorOnly()
	{
		$contributorList = [5];

		self::$postData->where(function($query) use($contributorList) {
			$query->whereIn('user_id', $contributorList);
		});
	}

	// ------------------------------------------------------------------------

	private static function setDateRange($dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{
		// If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		if ($dateRange == 'all-time') { return; }

		// ------------------------------------------------------------------------
		
		// Start Date and End Date are exist?
		if ($startDate AND $endDate)
		{
			self::$postData->where(function($query) use($startDate, $endDate) {
				$query->whereBetween('created_on', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
			});
			return;
		}

		// ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				self::$postData->whereRaw("DATE(posts.created_on) = DATE(CURDATE())");
				break;
			case 'yesterday':
				self::$postData->whereRaw("DATE(posts.created_on) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
				break;
			case 'last-7-days':
				self::$postData->whereRaw('DATE(posts.created_on) = DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
				break;
			case 'last-30-days':
				self::$postData->whereRaw('DATE(posts.created_on) = DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
				break;
			case 'last-90-days':
				self::$postData->whereRaw('DATE(posts.created_on) = DATE_SUB(CURDATE(), INTERVAL 90 DAY)');
				break;
			case 'this-month':
				self::$postData->whereRaw('DATE_FORMAT(posts.created_on, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")');
				break;
			case 'this-year':
				self::$postData->whereRaw("YEAR(posts.created_on) = YEAR(CURDATE())");
				break;
		}
	}

	// ------------------------------------------------------------------------
	
	private static function setStatus($status = 'all-status')
	{
		switch ($status)
		{
			case 'private':
				self::$postData->where('status', 2);
				break;
			case 'public':
				// Get moderated, unpublished and publised
				self::$postData->where(function($query) {
					$query->whereIn('status', [-2, 0, 1]);
				});
				break;
			case 'approved':
				self::$postData->where('status', 1);
				break;
			case 'moderated':
				self::$postData->where('status', -2);
				break;
			case 'rejected':
				self::$postData->where('status', 0);
				break;
			case 'all-status':
			default;
				self::$postData->where(function($query) {
					$query->whereNotIn('status', [-1, -99]);
				});
				break;
		}
	}

	// ------------------------------------------------------------------------
	
	private static function setSort($sortBy = 'created', $reverse = TRUE)
	{
		$reverse = (!$reverse || ($reverse == 'false') ? 'ASC' : 'DESC');

		switch ($sortBy)
		{
			case 'channel':
				self::$postData->orderBy('channel_id', $reverse);
				break;
			case 'format':
				self::$postData->orderBy('post_type', $reverse);
				break;
			case 'view':
				self::$postData->orderBy('view', $reverse);
				break;
			case 'share':
				self::$postData
					 ->selectRaw('`posts`.*, (SELECT `post_shares`.`shares` FROM `post_shares` WHERE `post_shares`.`post_id` = `posts`.`id`) as `share_count`')
					 ->orderBy('share_count', $reverse);
				break;
			case 'embed':
				self::$postData
					 ->selectRaw('`posts`.*, (SELECT COUNT(`post_embed`.`id_embed`) FROM `post_embed` WHERE `post_embed`.`id_post` = `posts`.`id`) as `embed_count`')
					 ->orderBy('embed_count', $reverse);
				break;
			case 'created':
			default:
				self::$postData->orderBy('created_on', $reverse);
				break;
		}
	}

	// ------------------------------------------------------------------------
	
	private static function setSearch($search = FALSE)
	{
		self::$postData->where(function($query) use ($search) {
			$query->whereRaw('MATCH(posts.title) AGAINST ("' . $search . '")');
		});
	}

	public function getTagsByPost($post_id){
      	$post = $this->where('id','=',$post_id);
      	$tags = [];
      	if($post->get()[0]){
        	$post_tags = $post->get()[0]->postTags();
        	foreach ($post_tags->get() as $key => $post_tag) {
          		$tag = $post_tag->tag()->first();
          		$tags[] = array(['id'=> $tag->id, 'slug'=> $tag->slug, 'title'=> $tag->title]);
        	}
      	}
      	return $tags;
    }

	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function user()
	{ return $this->belongsTo('App\User'); }

	// ------------------------------------------------------------------------
	
	public function image()
	{ return $this->belongsTo('App\Image', 'object_file_id'); }

	// ------------------------------------------------------------------------
	
	public function channel()
	{ return $this->belongsTo('App\Channel', 'channel_id'); }

	// ------------------------------------------------------------------------
	
	public function share()
	{ return $this->hasOne('App\Share'); }

	// ------------------------------------------------------------------------
	
	public function embed()
	{ return $this->hasMany('App\Embed', 'id_post'); }

	// ------------------------------------------------------------------------
	
	public function postTag()
	{ return $this->hasMany('App\PostTag'); }

	// ------------------------------------------------------------------------
	
	public function tag()
	{ return $this->belongsToMany('App\Tag', 'posts_tags'); }
}