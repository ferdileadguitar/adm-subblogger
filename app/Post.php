<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Channel;
use App\ObjectFile;
use Carbon\Carbon;
use DB;

use App\Events\KeepoCache;

class Post extends Model
{
	public $timestamps  = false;
	
	protected $guarded  = [];

	protected $events   = [
		'updated'		=> Events\KeepoCache::class
	];

	private $__instance  = null;
	private $postData    = false;

	private $request     = false;

	// ------------------------------------------------------------------------
	// Public Methods
	// ------------------------------------------------------------------------

	public function __construct()
	{
		$this->request = Request();
	}

	public function getInstance()
	{
		if ($this->__instance === null)
		{ $this->__instance = new self; }

		return $this->__instance;
	}

	// ------------------------------------------------------------------------
	
	public function getFiltered($request = FALSE, $bindStatus = FALSE)
	{
		// Init
		$postData  = $this->with('user', 'objectFile', 'channel', 'tag', 'share', 'postsMsg', 'embed'); // its not allowed to set global variable as $this->postData
		
		// Group By with current post_type list
		$postData = $postData->whereIn('posts.post_type', config('list.post_type'));

		// Group by with current channels
		// $postData = $postData->whereIn('channels.slug', config('list.channel'));

		// ------------------------------------------------------------------------
		
		// Contributor only
		if ($request->input('contributor'))
		{ $this->setContributorOnly($postData); }

		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ $this->setDateRange($postData, $dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ $this->setDateRange($postData, FALSE, $startDate, $endDate); }

		// Status
		if ($status = $request->input('status') AND $request->method() != 'PUT' OR ($bindStatus))
		{ 
			$status = ($bindStatus) ? $bindStatus : $status;
			$this->setStatus($postData, $status); // this is how u should set pass data inside model
		}

		if ($search = $request->input('users'))
		{ $this->setUsers($postData, $search); }

		if ($sort = $request->input('key'))
		{ $this->setSort($postData, $sort, $request->input('reverse')); }

		// Search
		if ($search = $request->input('search'))
		{ $this->setSearch($postData, $search); }


		return $postData;
	}

	// ------------------------------------------------------------------------
	
	public function cleanPaginate($take = 50, $page = 0)
	{
		// Init
		$postData  = $this->getFiltered($this->request);

		$total     = @DB::table(DB::raw("({$postData->toSql()}) as ttl_post"))->setBindings($postData->getBindings())->select(DB::raw('COUNT(*) total'))->first()->total;
		$paginate  = $postData->groupBy('posts.id')->paginate($take)->toArray();

		$page      = ($this->request->input('page') < 2) ? $page : ($this->request->input('page') - 1) * $take;

		$paginate  =  $paginate['data'] ? $paginate : ['data'=> $postData->skip($page)->take($take)->get()->toArray(), 'total' => $total, 'last_page' => (int) ceil($total / $take), 'current_page' => (int) $this->request->input('page')];

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
								'id' 	=>  @$post['object_file']['id'],
								'url' 	=>  preg_replace('/https?\:/', '', @$post['object_file']['full_path']),
								'name' 	=>  @$post['object_file']['file_name']
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
				'is_up_contents' => $post['is_up_contents'],
				
				// User
				'user'       => array(
					'id'			=> $post['user']['id'],
					'display_name' 	=> @$post['user']['display_name'],
					'url'   		=> $post['user'] ? url(implode(['users', $post['user']['slug']], '/')) : null,
					'slug'			=> @$post['user']['slug']
				),	
				'source' 	=> $post['source'],
				// Tags
				'tags'       => collect(@$post['tag'])->map(function($tag) {
					return [
						'id'    => $tag['id'],
						'title' => $tag['title']
					];
				}),

				// Reject Msg
				'reject_msg' => @$post['postsMsg']['message']
			];

		});

		$paginate = collect([
						'all_post'       => $this->countAllPost($postData), 
                        'rejected_post'  => $this->countRejected($postData), 
                        'public_post'    => $this->countPublic($postData), 
                        'approved_post'  => $this->countApproved($postData), 
                        'moderated_post' => $this->countAllModerated($postData),
                        'private_post'   => $this->countPrivate($postData),
			        ])->merge($paginate);
		
		return $paginate;	
	}

	// ------------------------------------------------------------------------
	

	public function countModerated()
	{
		// return $this->where('posts.status', -2)->count();
	}
	
	public function countAllPost($postData = false) {
		$postData = $this->getFiltered($this->request, 'all-post');

		$total    = @DB::table(DB::raw("({$postData->groupBy('posts.id')->toSql()}) as ttl_post"))->setBindings($postData->getBindings())->selectRaw('COUNT(*) total')->first()->total;

		return $total;
	}	

	public function countAllModerated($postData = false) 
	{	
		$postData = $this->getFiltered($this->request, 'moderated');

		$total    = @DB::table(DB::raw("({$postData->groupBy('posts.id')->toSql()}) as ttl_post"))->setBindings($postData->getBindings())->selectRaw('COUNT(*) total')->first()->total;
		
		return $total;
	}

	public function countApproved($postData = false) 
	{	
		$postData = $this->getFiltered($this->request, 'approved');

		$total    = @DB::table(DB::raw("({$postData->groupBy('posts.id')->toSql()}) as ttl_post"))->setBindings($postData->getBindings())->selectRaw('COUNT(*) total')->first()->total;
		
		return $total;
	}

	public function countPublic($postData = false) 
	{	
		$postData = $this->getFiltered($this->request, 'public');

		$total    = @DB::table(DB::raw("({$postData->groupBy('posts.id')->toSql()}) as ttl_post"))->setBindings($postData->getBindings())->selectRaw('COUNT(*) total')->first()->total;
		
		return $total;
	}

	public function countRejected($postData = false) 
	{	
		$postData = $this->getFiltered($this->request, 'rejected');

		$total    = @DB::table(DB::raw("({$postData->groupBy('posts.id')->toSql()}) as ttl_post"))->setBindings($postData->getBindings())->selectRaw('COUNT(*) total')->first()->total;
		
		return $total;
	}

	public function countPrivate($postData = false) 
	{	
		$postData = $this->getFiltered($this->request, 'private');

		$total    = @DB::table(DB::raw("({$postData->groupBy('posts.id')->toSql()}) as ttl_post"))->setBindings($postData->getBindings())->selectRaw('COUNT(*) total')->first()->total;
		
		return $total;
	}


	// ------------------------------------------------------------------------
	
	public function updateStatus($postID = FALSE, $status = 0)
	{
		if (empty($postID)) { return ['error' => 'Post not found']; }
		// ------------------------------------------------------------------------
		if (is_array($postID)) { $post = $this->whereIn('id', $postID); }
		else { $post = $this->where('id', $postID); }

		$post->update(['status' => $status]);

		// Flush cache
		event(new KeepoCache($post));

		return [
			'all_post'        => $this->countAllPost(), 
			'public_post'     => $this->countPublic(), 
			'approved_post'   => $this->countApproved(), 
			'moderated_post'  => $this->countAllModerated(), 
			'rejected_post'   => $this->countRejected(),
			'privated_post'   => $this->countPrivate(),
		];
	}

	// ------------------------------------------------------------------------
	
	public function updateStickyPremium($postID = FALSE, $stickyOrPremium = 'sticky', $set = FALSE)
	{
		if (empty($postID)) { return ['error' => 'Post not found']; }

		// ------------------------------------------------------------------------
		
		if (is_array($postID)) { $post = $this->whereIn('id', $postID); }
		else { $post = $this->where('id', $postID); }

		$post->update([$stickyOrPremium => $set]);

		// Flush cache
		event(new KeepoCache($post));
	}

	public function updatePostTitle($postID = FALSE, $postTitle = FALSE) {
		if( empty($postTitle) ) { return ['error' => 'Post not found']; }


		$post = $this->where(function($query) use ($postID, $postTitle) {
			$query->where(['id' => $postID]);
				
			$title = preg_replace('~<br\s?\/?>$~ixu', '', $postTitle);

	        // Removing blank space at the of context
	        $title = preg_replace('~(\&nbsp\;|\&amp\;nbsp\;)+$~', '', $postTitle );
	        $title = htmlentities($postTitle, ENT_QUOTES, 'UTF-8');

			$query->update(['title' => $title]);
			
			return $query;
		});

		// Flush cache
		event(new KeepoCache($post));
		
		$post = $post->first();
		
		return ['title' => $post->title, 'slug' => $post->slug, 'url' => implode([config('app.keepo_url'), $post->user->display_name, $post->slug], '/')];
	} 

	public function updatePostChannel($postID = false, $postChannel = FALSE) {
		if ( empty($postID) ) { return ['error' => 'Post not found']; }

		$post = $this->with('channel')->where(function($query) use ($postChannel, $postID) {
			$channelID = Channel::where([ 'slug' => $postChannel ]);

			$query->where(['id' => $postID]);
			$query->update([ 'channel_id' => $channelID->first()->id ]);

			return $query;
		});	

		// Flush cache
		event(new KeepoCache($post));

		$post = $post->first();

		return ['channel' => array( 'name' => html_entity_decode($post->channel->title), 'slug' => $post->channel->slug ) ];
	}

	public function updatePostImageCover($postID = false, $postImage = array()) {
		$objectFile  = array();

		if ( empty($postID) ) { return ['error' => 'Post not found']; }

		// Is image exist
		$objectFile = objectFile::where(['id' => $postImage['id']])->first();

		if(is_null($objectFile)) {
			$objectFile = array('id' => 1, 'file_name' => null, 'full_path' => null);
		}

		$post  = $this->with('objectFile')->where(function($query) use ($postID, $postImage, $objectFile) {

			$query->where(['id' => $postID]);
			$query->update(['object_file_id' => $objectFile['id']]);

			return $query;
		});


		// Should s not empty
		if ( !empty($post->first()) AND !empty($objectFile) )
			$response = ['image' => array(
								'id' 	=> $objectFile['id'], 
								'name' 	=> $objectFile['file_name'], 
								'url'	=> preg_replace('/https?\:/', '', @$objectFile['full_path'])
							)
						];
		else
			return ['error' => 'Failed to post'];
		
		// Flush Cache
		event(new KeepoCache($post));

		// Return data
		return $response;		
	}

	public function updatePostCreated($postID = FALSE, $postCreated = FALSE) {
		if ( empty($postID) ) { return ['error' => 'Post not found']; }
		$convDate = date('Y-m-d H:i:s', strtotime($postCreated));

		$post     = $this->where(function($query) use ($postID, $postCreated, $convDate) {
			$query->where(['id' => $postID])->update(['created_on' => $convDate]);
			return $query;
		});
		
		$newDate = date('d M Y H:i', strtotime($post->first()->created_on));
		
		// Flush cache
		event(new KeepoCache($post));

		return ['created' => $newDate];
	}

	public function updatePostUpContent($postID = FALSE, $postCreated = FALSE) {
		if ( empty($postID) ) { return ['error' => 'Post not found']; }
		
		$convDate = date('Y-m-d H:i:s', strtotime($postCreated));

		$post  = $this->where(function($query) use ($postID, $convDate) {
			$query->where(['id' => $postID])->update(['is_up_contents' => 1,'created_on' => $convDate]);
			return $query;
		});

		$newDate  = date('d M Y H:i', strtotime($post->first()->created_on));

		// Flush cache
		event(new KeepoCache($post));

		return ['created' => $newDate, 'is_up_contents' => $post->first()->is_up_contents];
	}

	public function updatePostFeeds($post = array(), $postID = FALSE, $response = array()) {
		if ( empty($postID) ) { return ['error' => 'Post no found']; }

		$post = $this->with('user', 'objectFile', 'channel', 'tag', 'share', 'embed', 'postsMsg')->where(function($query) use ($post, $postID) {
					$query->where(['id' => $postID->id])->update($post);
					return $query;
				});

		// Flush cache
		event(new KeepoCache($post));

		$post = $post->first()->toArray();

		$response = collect($response)->push($post)->map(function($items) {
			return [
				'id'         => $items['id'],
				'title'      => html_entity_decode($items['title'], ENT_QUOTES),
				'lead'       => html_entity_decode($items['lead'], ENT_QUOTES),
				'slug'       => str_slug($items['slug']),
				// 'url'        => implode(['https://keepo.me', @$items['user']['username'], $items['slug']], '/'),
				'url'        => implode([config('app.keepo_url'), @$items['user']['username'], $items['slug']], '/'),
				'image'      => array(
								'id' 	=>  @$items['object_file']['id'],
								'url' 	=>  preg_replace('/https?\:/', '', @$items['object_file']['full_path']),
								'name' 	=>  @$items['object_file']['fill_name']
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
					'url'   		=> $items['user'] ? url(implode(['users', $items['user']['slug']], '/')) : null,
				),	
				'source' 	=> $items['source'],
				
				// Tags
				'tags'       => collect(@$items['tag'])->map(function($tag) {
					return [
						'id'    => $tag['id'],
						'title' => $tag['title']
					];
				}),

				// Reject Msg
				'reject_msg' => @$items['postsMsg']['message']
			];
		});

		return $response->first();
	}

	// ------------------------------------------------------------------------
	// Private Methods
	// ------------------------------------------------------------------------
	
	private function setContributorOnly($model)
	{
		$contributorList = config('list.contributor');

		return $model->where(function($query) use($contributorList) {
			$query->whereIn('posts.user_id', $contributorList);
		});
	}

	// ------------------------------------------------------------------------

	private function setDateRange($model, $dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{
		// If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		if ($dateRange == 'all-time') { return; }

		// ------------------------------------------------------------------------
		
		// Start Date and End Date are exist?
		if ($startDate AND $endDate)
		{
			return $model->where(function($query) use($startDate, $endDate) {
				// $query->whereBetween('posts.created_on', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
				$query->whereRaw('`posts`.`created_on` BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"');
			});
		}

		// ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				$model->whereRaw("DATE(posts.created_on) = DATE(CURDATE())");
				break;
			case 'yesterday':
				$model->whereRaw("DATE(posts.created_on) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
				break;
			case 'last-7-days':
				$model->whereRaw('DATE(posts.created_on) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
				break;
			case 'last-30-days':
				$model->whereRaw('DATE(posts.created_on) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
				break;
			case 'last-90-days':
				$model->whereRaw('DATE(posts.created_on) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)');
				break;
			case 'this-month':
				$model->whereRaw('DATE_FORMAT(posts.created_on, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")');
				break;
			case 'this-year':
				$model->whereRaw("YEAR(posts.created_on) = YEAR(CURDATE())");
				break;
		}

		return $model;
	}

	// ------------------------------------------------------------------------
	
	private function setStatus($model, $status = 'all-status')
	{
		// $this->getFiltered($this->request);
		switch ($status)
		{	
			case 'all-post':
				// Get moderated, unpublished and publised
				$model->where(function($query) {
					$query->whereIn('posts.status', [-2, 0, 1, 2]);
				});
				break;
			case 'private':
				$model->where('posts.status', 2);
				break;
			case 'public':
				// Get moderated, unpublished and publised
				$model->where(function($query) {
					$query->whereIn('posts.status', [-2, 0, 1]);
				});
				break;
			case 'approved':
				$model->where('posts.status', 1);
				break;
			case 'moderated':
				$model->where('posts.status', -2);
				break;
			case 'rejected':
				$model->where('posts.status', 0);
				break;
			case 'all-status':
			default;
				$model->where(function($query) {
					$query->whereNotIn('posts.status', [-99, -1]); // -1 is unpublish right
				});
				break;
		}

		return $model;
	}

	// ------------------------------------------------------------------------
	
	private function setSort($model, $sortBy = 'created', $reverse = TRUE){
		$reverse = (!$reverse || ($reverse == 'false') ? 'ASC' : 'DESC');

		switch ($sortBy)
		{
			case 'channel':
				$model
					 ->selectRaw('`posts`.*, (SELECT `channels`.`title` FROM `channels` WHERE `channels`.`id` = `posts`.`channel_id` LIMIT 50) as `channel_title`')
					 ->orderBy('channel_title', $reverse);
				break;
			case 'format':
				// dd( $this->postData->toSql() );
				$model->orderBy('posts.post_type', $reverse);
				break;
			case 'view':
				$model->orderBy('posts.views', $reverse);
				break;
			case 'mv':
				$model->orderBy('posts.views', $reverse);
				break;
			case 'sr':
				$model
					 ->selectRaw('`posts`.*, (SELECT SUM(`post_shares`.`shares`) FROM `post_shares` WHERE `post_shares`.`post_id` = `posts`.`id` LIMIT 50) as `share_count`')
					 ->orderBy('share_count', $reverse);
				break;
			case 'share':
				$model
					 ->selectRaw('`posts`.*, (SELECT SUM(`post_shares`.`shares`) FROM `post_shares` WHERE `post_shares`.`post_id` = `posts`.`id` LIMIT 50) as `share_count`')
					 ->orderBy('share_count', $reverse);
				break;
			case 'embed':
				$model
					 ->selectRaw('`posts`.*, (SELECT COUNT(`post_embed`.`id_embed`) FROM `post_embed` WHERE `post_embed`.`id_post` = `posts`.`id`) as `embed_count`')
					 ->orderBy('embed_count', $reverse);
				break;
			case 'n':
				$model->orderBy('posts.created_on', $reverse);
				break;
			case 'created':
			default:
				$model->orderBy('posts.created_on', $reverse);
				break;
		}

		return $model;
	}

	// ------------------------------------------------------------------------
	
	private function setSearch($model, $search = FALSE)
	{
		return $model->where(function($query) use ($search) {
			$query->whereRaw('MATCH(posts.title) AGAINST ("' . $search . '")');
		});
	}

	private function setUsers($model, $search = FALSE)
	{
		return $model->where('posts.user_id', '=', $search);
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
	{ return $this->belongsTo('App\User', 'user_id'); }

	// ------------------------------------------------------------------------

	public function objectFile()
	{ return $this->belongsTo('App\ObjectFile', 'object_file_id'); }

	// ------------------------------------------------------------------------
	
	public function channel()
	{ return $this->belongsTo('App\Channel', 'channel_id'); }

	// ------------------------------------------------------------------------
	
	public function share()
	{ return $this->hasOne('App\Share')->selectRaw('post_id, shares'); }

	// ------------------------------------------------------------------------
	

	public function postsMsg()
	{ return $this->hasOne('App\PostRejectedMsg'); }
	// ------------------------------------------------------------------------

	public function sumShares()
	{ 
		return $this->share()->selectRaw('CAST(SUM(`post_shares`.`addon` + `post_shares`.`shares`) as UNSIGNED) as "total_shares"')->groupBy('post_id'); 
	}
	// ------------------------------------------------------------------------

	public function embed()
	{ return $this->hasMany('App\Embed', 'id_post'); }

	// ------------------------------------------------------------------------

	public function embedLog()
	{ return $this->hasMany('App\EmbedLog', 'post_id')->selectRaw('user_id, shareid, post_id'); }
	
	public function channelEmbedLog()
	{ return $this->hasMany('App\EmbedLog', 'post_id'); }
	// ------------------------------------------------------------------------


	public function sumEmbed()
	{ 
		return $this->embed()->select(DB::raw('shareid, view, CAST(SUM(`post_embed`.`view`) as UNSIGNED) AS "total_embed"'))->groupBy('id_post', 'shareid', 'view'); 
	}

	// ------------------------------------------------------------------------
	public function postTag()
	{ return $this->hasMany('App\PostTag'); }

	// ------------------------------------------------------------------------
	
	public function tag()
	{ return $this->belongsToMany('App\Tag', 'posts_tags'); }
}
