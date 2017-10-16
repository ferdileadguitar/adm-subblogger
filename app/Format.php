<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Format extends Model
{

	public $table = 'posts';

	private static $formatList    = ['article', 'listicle', 'meme', 'gallery', 'funquiz', 'convo', 'quickpersonality', 'quicktrivia', 'quickpolling','cardclick'];
	private static $__instance  = null;
	private static $formatData    = false;

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
		
		self::$formatData   = self::$__instance;
		// self::$formatData = self::selectRaw('post_type, SUM(posts.views) as total_views, AVG(posts.views) as average_views');
		// self::$formatData = self::selectRaw('post_type');
		self::$formatData   = self::$formatData->selectRaw('posts.post_type as title');
		self::$formatData   = self::$formatData->selectRaw('COUNT(*) as total_posts');
		self::$formatData   = self::$formatData->selectRaw('CAST(SUM(`posts`.`views`) as UNSIGNED) AS total_views');
		self::$formatData   = self::$formatData->selectRaw('CAST(AVG(`posts`.`views`) as UNSIGNED) AS average_views');
		self::$formatData   = self::$formatData->selectRaw('CAST(COALESCE(SUM(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) as UNSIGNED) AS total_shares');
		self::$formatData   = self::$formatData->selectRaw('CAST(COALESCE(AVG(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) as UNSIGNED) AS average_shares');
		self::$formatData   = self::$formatData->selectRaw('CAST(COUNT(`view_logs_embed`.`post_id`) as UNSIGNED) AS total_embed');
		self::$formatData   = self::$formatData->selectRaw('CAST(COALESCE(AVG(`view_logs_embed`.`post_id`), 0) as UNSIGNED)AS average_embed');
		// self::$formatData   = self::$formatData->selectRaw('DATE_FORMAT(`posts`.`created_on`, "%d %M %Y %H:%i:%s") AS created_on');

		self::$formatData   = self::$formatData->leftJoin('post_shares', 'post_shares.id', '=', 'posts.id');
		self::$formatData   = self::$formatData->leftJoin('view_logs_embed', 'view_logs_embed.post_id', '=', 'posts.id');
		
		self::$formatData = self::$formatData->whereIn('post_type', self::$formatList);

		// self::$formatData = self::$formatData->distinct('format');
		self::$formatData = self::$formatData->groupBy('post_type', 'fb', 'twitter', 'shares', 'addon','post_shares.post_id');
		
		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }

		return self::$__instance;
	}

	public static function cleanPaginate($take = 50) 
	{
		$paginate = self::$formatData->paginate($take)->toArray();

		// $paginate['data'] = collect($paginate['data'])->map(function($item) {

		// 	// Shares
		// 	$total_shares  = collect($item['share'])->count();
			
		// 	// Embed
		// 	$total_embed   = collect($item['embed_log'])->count();

		// 	return [

		// 		// Agregate
		// 		'total_shares'		=> @$total_shares,
		// 		'total_embed' 		=> @$total_embed
		// 	];
		// });
		return $paginate;
	}

	private static function setDateRange($dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{
		// $qryEmbed = null; // qry embed 
		// $qryPosts = null; // qry posts

		// // If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		// if ($dateRange == 'all-time') { $qryEmbed;$qryPosts; }

		// // ------------------------------------------------------------------------
		
		// // Start Date and End Date are exist?
		// if ($startDate AND $endDate)
		// {
		// 	$qryPosts = '`posts`.`created_on` BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
		// 	$qryEmbed = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
		// }

		// // ------------------------------------------------------------------------
		
		// switch ($dateRange) 
		// {
		// 	case 'today':
		// 		$qryPosts   = 'DATE(`posts`.`created_on`) = DATE(CURDATE())';
		// 		$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE(CURDATE())';
		// 		break;
		// 	case 'yesterday':
		// 		$qryPosts   = 'DATE(`posts`.`created_on`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
		// 		$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
		// 		break;
		// 	case 'last-7-days':
		// 		$qryPosts   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
		// 		$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
		// 		break;
		// 	case 'last-30-days':
		// 		$qryPosts   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
		// 		$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
		// 		break;
		// 	case 'last-90-days':
		// 		$qryPosts   = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
		// 		$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
		// 		break;
		// 	case 'this-month':
		// 		$qryPosts   = 'DATE_FORMAT(`posts`.`created_on`, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
		// 		$qryEmbed   = 'DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
		// 		break;
		// 	case 'this-year':
		// 		$qryPosts   = 'YEAR(`posts`.`created_on`) = YEAR(CURDATE())';
		// 		$qryEmbed   = 'FROM_UNIXTIME(`view_logs_embed`.`last_activity`) = YEAR(CURDATE())';
		// 		break;
		// }

		// self::$authorsData->with([
		// 	'posts' => function($query) use ($dateRange, $qryPosts){
		// 		if(!is_null($qryPosts))
		// 			$query->whereRaw($qryPosts);

		// 		$query->select('user_id', 'id', DB::raw('CAST(SUM(`posts`.`views`) as UNSIGNED) as total_views'));
		// 		$query->groupBy('posts.user_id', 'posts.id');
		// 	},
		// 	'embedLog' => function($query) use ($dateRange, $qryEmbed)
		// 	{ if(!is_null($qryEmbed)) $query->whereRaw($qryEmbed); }
		// ]);
	}

	/*==========================================
					RELATIONSHIP
	============================================*/
	// public function posts()
	// {  }

	public function share()
	{ 
		$collection = $this->hasOne('App\Share', 'post_id');

		$collection = $collection->selectRaw('post_id, CAST(SUM(fb + twitter + shares + addon) as UNSIGNED) as total_shares');

		$collection = $collection->groupBy('fb', 'twitter', 'shares', 'addon', 'post_id');

		return $collection;
	}

	public function embedLog()
	{
		$collection = $this->hasMany('App\EmbedLog', 'post_id');

		$collection = $collection->selectRaw('`view_logs_embed`.`user_id`, `view_logs_embed`.`post_id`, `view_logs_embed`.`shareid`, `view_logs_embed`.`last_activity`, DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m-%d %H:%i:%s") as created_on');

		return $collection;
	}
}

/*===================
	FIRST ATTEMPT
=====================*/
/*
SELECT 	
	ts.`post_type` formats, # formats as title
	#coalesce(ttl_embd.cnt, 0) embed_all,  # ttl is total
	#COALESCE(ttl_shrs.cnt, 0) shares_all, # ttl is total
	COUNT(*) AS post,
	FORMAT(SUM(ts.`views`), 'de_DE') total_views,
	FORMAT(AVG(ts.`views`), 'de_DE') average_views,
	COALESCE(SUM(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) AS total_shares,
	COALESCE(AVG(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) AS average_shares,
	#COUNT(`view_logs_embed`.`post_id`) AS total_embed,
	COALESCE(range_embed.cnt, 0) AS total_embed,
	COALESCE( 
		( COALESCE(range_embed.cnt, 0) / COALESCE(ttl_embd.cnt, 0)) # total / n records
	, 0) average_embed,

	CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), '%d %M %Y'), ' - ', DATE_FORMAT(CURDATE(), '%d %M %Y')) AS date_range # date range
	# Main Tables
	FROM posts ts
	# Join
	LEFT JOIN `post_shares` ON `post_shares`.`post_id` = ts.`id`
	LEFT JOIN `view_logs_embed` ON `view_logs_embed`.`post_id` = ts.`id`
	
	LEFT OUTER JOIN (SELECT COUNT(*) cnt FROM `view_logs_embed`) ttl_embd ON `view_logs_embed`.`post_id` = ts.`id`
	LEFT OUTER JOIN (SELECT COUNT(*) cnt FROM `post_shares`) ttl_shrs ON `post_shares`.`post_id` = ts.`id`
		
	LEFT OUTER JOIN (
		SELECT `view_logs_embed`.`post_id` embed_log_id, COUNT(*) cnt
		FROM `view_logs_embed`
		LEFT JOIN `posts` ON `view_logs_embed`.`post_id` = `posts`.`id`
		WHERE FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
		GROUP BY `posts`.`post_type`
	) range_embed ON range_embed.embed_log_id = ts.`id`
	
	WHERE ts.`post_type`
		IN ('article', 'listicle', 'funquiz', 'gallery', 'meme', 'convo', 'cardquiz', 'quickpersonality', 'quicktrivia', 'quickpolling') 
	GROUP BY ts.`post_type`
	#ORDER BY range_embed.cnt DESC
	ORDER BY average_embed DESC
*/