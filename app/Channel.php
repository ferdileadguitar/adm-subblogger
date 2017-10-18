<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Post;

class Channel extends Model
{
	protected $fillable = ['id', 'title'];
	// private static $channelList = ['entertainments-channel'];

	private static $channelList = ['hobbies-channel', 'animals-channel', 'creepy-channel', 'entertainments-channel', 'facts-channel', 'anime-comic-channel', 'inspirational-channel', 'lifestyle-channel', 'fun-humor-channel', 'news-info-channel', 'nsfw-channel', 'wtf-channel', 'sports-channel', 'tech-channel', 'traveling-channel', 'unique-weird-channel', 'meme', 'fun-quiz'];
	protected $guarded = [];
	private static $__instance  = null;
	private static $channelsData = false;

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

		self::$channelsData = self::with('posts', 'postsViews', 'embed', 'share');

		self::$channelsData = self::$channelsData->selectRaw('`channels`.`id`, `channels`.`title`, `channels`.`slug`');

		self::$channelsData = self::$channelsData->selectRaw('(SELECT COUNT(*) FROM `post_shares`) as all_total_shares');

		self::$channelsData = self::$channelsData->selectRaw('(SELECT COUNT(*) FROM `view_logs_embed`) as all_total_embed');

		self::$channelsData = self::$channelsData->whereIn('slug', self::$channelList);
		
		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }

		return self::$__instance;
	}

	public static function cleanPaginate($take = FALSE){
		// dd( self::$channelsData->toSql() );
		$paginate = self::$channelsData->paginate($take)->toArray();

		// dd( $paginate['data'] );
		$paginate['data'] = collect($paginate['data'])->map(function($item) {

			// Posts
			$total_posts    = collect($item['posts'])->count();
			$total_views    = collect($item['posts_views'])->sum('views');
			$average_views  = (empty($total_posts) || empty($total_views) ? 0 : ($total_views / $total_posts));

			// Shares
			$total_shares   = collect($item['share'])->count();
			$average_shares = number_format($total_shares / $item['all_total_shares'], 4);

			// Embed
			$total_embed    = collect($item['embed'])->count();
			$average_embed  = number_format($total_embed / $item['all_total_embed'], 4);

			return [
				'id' 				=> @$item['id'],
				'title' 			=> @$item['title'],
				'slug' 	 		   	=> @$item['slug'],

				// Agregate
				// Posts
				'total_posts'		=> @$total_posts,
				'total_views'		=> @$total_views,
				'average_views'		=> @$average_views,
				
				// Shares
				'total_shares'		=> @$total_shares,
				'average_shares'	=> @$average_shares,

				// Embed
				'total_embed' 		=> @$total_embed,
				'average_embed' 	=> @$average_embed
			];
		});

		return $paginate;
	}

	private static function setDateRange($dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{
		$qryEmbed = null; // qry embed 
		$qryPosts = null; // qry posts

		// If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		if ($dateRange == 'all-time') { $qryEmbed;$qryPosts; }

		// ------------------------------------------------------------------------
		
		// Start Date and End Date are exist?
		if ($startDate AND $endDate)
		{
			$qryPosts = '`posts`.`created_on` BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
			$qryEmbed = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
		}

		// ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				$qryPosts   = 'DATE(`posts`.`created_on`) = DATE(CURDATE())';
				$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE(CURDATE())';
				break;
			case 'yesterday':
				$qryPosts   = 'DATE(`posts`.`created_on`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
				$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
				break;
			case 'last-7-days':
				$qryPosts   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				break;
			case 'last-30-days':
				$qryPosts   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				break;
			case 'last-90-days':
				$qryPosts   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				break;
			case 'this-month':
				$qryPosts   = 'DATE_FORMAT(`posts`.`created_on`, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				$qryEmbed   = 'DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				break;
			case 'this-year':
				$qryPosts   = 'YEAR(`posts`.`created_on`) = YEAR(CURDATE())';
				$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) = YEAR(CURDATE())';
				break;
		}

		self::$channelsData->with([
			'posts' => function($query) use ($dateRange, $qryPosts){
				if(!is_null($qryPosts))
					$query->whereRaw($qryPosts);

				// $query->select('user_id', 'id', DB::raw('CAST(SUM(`posts`.`views`) as UNSIGNED) as total_views'));
				// $query->groupBy('posts.user_id', 'posts.id');
			},
			'postsViews' => function($query) use ($dateRange, $qryPosts){
				if(!is_null($qryPosts))
					$query->whereRaw($qryPosts);

				// $query->select('user_id', 'id', DB::raw('CAST(SUM(`posts`.`views`) as UNSIGNED) as total_views'));
				// $query->groupBy('posts.user_id', 'posts.id');
			},
			'embed' => function($query) use ($dateRange, $qryEmbed)
			{ if(!is_null($qryEmbed)) $query->whereRaw($qryEmbed); }
		]);
	}
	
	// ------------------------------------------------------------------------
	// Relations
	// ------------------------------------------------------------------------
	
	public function posts()
	{ return $this->hasMany('App\Post')->select('id', 'title', 'channel_id', 'views', 'created_on'); }

	public function postsViews(){
		$collection = $this->posts()->select('id', 'title', 'channel_id', 'views', 'created_on');

		return $collection;
	}

	public function embed()
	{ 
		$collection = $this->hasManyThrough('App\EmbedLog', 'App\Post', 'channel_id', 'post_id');

		$collection = $collection->selectRaw('post_id, DATE_FORMAT(FROM_UNIXTIME(last_activity), "%Y-%m-%d %H:%i:%s") as created_on');
		
		return $collection;
	}

	public function share()
	{ 
		$collection = $this->hasManyThrough('App\Share', 'App\Post', 'channel_id');

		$collection = $collection->selectRaw('post_id, CAST(SUM(fb + twitter + shares + addon) as UNSIGNED) as total_shares');

		$collection = $collection->groupBy('fb', 'twitter', 'shares', 'addon', 'post_id', 'channel_id');

		return $collection;
	}
}