<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Post;

class Channel extends Model
{	
	protected static $postDateRange   = null;
	protected static $embedDateRange  = null;

	protected $fillable = ['id', 'title'];
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

		self::$channelsData = self::$channelsData->whereIn('slug',  config('list.channel'));
		
		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }


			// Sort
		if ($sort = $request->input('key'))
		{ self::setSort($sort, $request->input('reverse')); }

		return self::$__instance;
	}

	public static function cleanPaginate($take = FALSE){
		$paginate = self::$channelsData->paginate($take)->toArray();

		// dd( $paginate );

		$paginate['data'] = collect($paginate['data'])->map(function($item) {

			// Posts
			$total_posts    = collect($item['posts'])->count();
			$total_views    = collect($item['posts_views'])->sum('views');
			$average_views  = (empty($total_posts) || empty($total_views) ? 0 : ($total_views / $total_posts));

			// Shares
			$total_shares   = collect($item['share'])->sum('total_shares');
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
				'average_views'		=> number_format(@$average_views, 2, '.', ''),
				
				// Shares
				'total_shares'		=> @$total_shares,
				'average_shares'	=> number_format(@$average_shares, 2, '.', ''),

				// Embed
				'total_embed' 		=> @$total_embed,
				'average_embed' 	=> number_format(@$average_embed, 2, '.', '')
			];
		});

		return $paginate;
	}

	private static function setSort($sortBy = 'created', $reverse = TRUE)
	{
		$reverse = (!$reverse || ($reverse == 'false') ? 'ASC' : 'DESC');
		
		// dd( self::$postDateRange );

		switch ($sortBy)
		{
			case 'post':
				$sql  = '`channels`.*, (SELECT COUNT(*) FROM `posts` WHERE `posts`.`channel_id` = `channels`.`id`';
				$sql .= is_null(self::$postDateRange) ? null : ' AND '.self::$postDateRange;
				$sql .= ') total_posts';
				self::$channelsData->selectRaw($sql)->orderBy('total_posts', $reverse);
				break;
			case 'view':
				$sql  = '`channels`.*, (SELECT SUM(`posts`.`views`) FROM `posts` WHERE `posts`.`channel_id` = `channels`.`id`';
				$sql .= is_null(self::$postDateRange) ? null : ' AND '.self::$postDateRange;
				$sql .= ') total_views';
				self::$channelsData->selectRaw($sql)->orderBy('total_views', $reverse);
				break;
			case 'avg-view':
				$sql  = '`channels`.*, (SELECT (SUM(`posts`.`views`) / (SELECT COUNT(*) FROM `posts`)) FROM `posts` WHERE `posts`.`channel_id` = `channels`.`id`';
				$sql .= is_null(self::$postDateRange) ? null : ' AND '.self::$postDateRange;
				$sql .= ') avg_views';
				self::$channelsData->selectRaw($sql)->orderBy('avg_views', $reverse);
				break;
			case 'share':
				
				// *note: Keepo IssueController line : 1552
				self::$channelsData
					->selectRaw('`channels`.*, (SELECT SUM(`post_shares`.`shares` + `post_shares`.`addon`) FROM `posts` LEFT JOIN `post_shares` ON `posts`.`id` = `post_shares`.`post_id` WHERE `posts`.`channel_id` = `channels`.`id`) total_shares')
					->orderBy('total_shares', $reverse);
				break;
			case 'avg-share':
				self::$channelsData
					->selectRaw('`channels`.*, (SELECT (SUM(`post_shares`.`shares` + `post_shares`.`addon`) / (SELECT COUNT(*) FROM `post_shares`)) FROM `posts` LEFT JOIN `post_shares` ON `posts`.`id` = `post_shares`.`post_id` WHERE `posts`.`channel_id` = `channels`.`id`) avg_shares')
					->orderBy('avg_shares', $reverse);
				break;
			case 'embed':
				self::$channelsData
					->selectRaw('`channels`.*, (SELECT COUNT(*) FROM `view_logs_embed` LEFT JOIN `posts` ON `posts`.`id` = `view_logs_embed`.`post_id` WHERE `posts`.`channel_id` = `channels`.`id`) total_embed')
					->orderBy('total_embed', $reverse);
				break;
			case 'avg-embed':
				self::$channelsData
					->selectRaw('`channels`.*, (SELECT ( COUNT(`view_logs_embed`.`post_id`) / (SELECT COUNT(*) FROM `view_logs_embed`) ) FROM `view_logs_embed` LEFT JOIN `posts` ON `posts`.`id` = `view_logs_embed`.`post_id` WHERE `posts`.`channel_id` = `channels`.`id`) avg_embed')
					->orderBy('avg_embed', $reverse);
				break;
			case 'created':
			default:
				self::$channelsData->orderBy('users.created_on', $reverse);
				break;
		}
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
			self::$postDateRange = '`posts`.`created_on` BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
			self::$embedDateRange = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
		}

		// ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				self::$postDateRange   = 'DATE(`posts`.`created_on`) = DATE(CURDATE())';
				self::$embedDateRange  = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = DATE(CURDATE())';
				break;
			case 'yesterday':
				self::$postDateRange   = 'DATE(`posts`.`created_on`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
				self::$embedDateRange  = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
				break;
			case 'last-7-days':
				self::$postDateRange   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				self::$embedDateRange  = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				break;
			case 'last-30-days':
				self::$postDateRange   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				self::$embedDateRange  = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				break;
			case 'last-90-days':
				self::$postDateRange   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				self::$embedDateRange  = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				break;
			case 'this-month':
				self::$postDateRange   = 'DATE_FORMAT(`posts`.`created_on`, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				self::$embedDateRange  = 'DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				break;
			case 'this-year':
				self::$postDateRange   = 'YEAR(`posts`.`created_on`) = YEAR(CURDATE())';
				self::$embedDateRange  = 'YEAR(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = YEAR(CURDATE())';
				break;
		}

		self::$channelsData->with([
			'posts' => function($query) use ($dateRange){
				if(!is_null(self::$postDateRange))
					$query->whereRaw(self::$postDateRange);

				// $query->select('user_id', 'id', DB::raw('CAST(SUM(`posts`.`views`) as UNSIGNED) as total_views'));
				// $query->groupBy('posts.user_id', 'posts.id');
			},
			'postsViews' => function($query) use ($dateRange){
				if(!is_null(self::$postDateRange))
					$query->whereRaw(self::$postDateRange);

				// $query->select('user_id', 'id', DB::raw('CAST(SUM(`posts`.`views`) as UNSIGNED) as total_views'));
				// $query->groupBy('posts.user_id', 'posts.id');
			},
			'embed' => function($query) use ($dateRange)
			{ if(!is_null(self::$embedDateRange)) $query->whereRaw(self::$embedDateRange); }
		]);

		// dd( self::$postDateRange );
		// return (object)['post' => self::$postDateRange, 'embed' => $qryEmbed];
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

		$collection = $collection->selectRaw('post_id, CAST(SUM(shares + addon) as UNSIGNED) as total_shares');

		$collection = $collection->groupBy('post_id', 'channel_id');

		return $collection;
	}
}