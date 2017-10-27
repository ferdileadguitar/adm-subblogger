<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Format extends Model
{

	public $table = 'posts';

	// private static $formatList    = ['article', 'listicle', 'meme', 'gallery', 'funquiz', 'convo', 'quickpersonality', 'quicktrivia', 'quickpolling','cardclick'];
	private static $formatList    = "'article', 'listicle', 'meme', 'gallery', 'funquiz', 'convo', 'quickpersonality', 'quicktrivia', 'quickpolling','cardclick', 'personality', 'trivia'";
	private static $__instance  = null;
    private static $formatData  = false;
	private static $formatPost  = false;

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
		
		$sql   = 'SELECT `posts`.`post_type` title,';
		
		// Post Views
		$sql  .= ' COALESCE(range_posts.cnt, 0) total_posts,';
		$sql  .= ' CAST(COALESCE(range_posts.cnt_views, 0) AS UNSIGNED) total_views,';
		$sql  .= ' CAST(COALESCE( ( COALESCE(range_posts.cnt_views, 0) / COALESCE(range_posts.cnt, 0)), 0) AS UNSIGNED) average_views,';

		// Post Shares 
		$sql  .= ' CAST(COALESCE(SUM(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) AS UNSIGNED) total_shares,';
		$sql  .= ' CAST(COALESCE(AVG(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) AS UNSIGNED) average_shares,';

		// Post Embed 
		$sql  .= ' COALESCE(range_embed.cnt, 0) total_embed,';
		$sql  .= ' (SELECT COUNT(*) cnt FROM `view_logs_embed`) all_embed,';
		$sql  .= ' COALESCE(range_embed.cnt, 0) / COALESCE((SELECT COUNT(*) cnt FROM `view_logs_embed`), 0) average_embed'; // total embed / n(all)
		// $sql  .= ' CAST(COALESCE(range_embed.cnt, 0) AS UNSIGNED) range_embed';

		// $sql  .= ' CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), "%d %M %Y"), " - ", DATE_FORMAT(CURDATE(), "%d %M %Y")) date_range';

		// Main Table Posts
		$sql  .= ' FROM `posts`';

		// Join Table
		$sql  .= ' LEFT JOIN `post_shares` ON `post_shares`.`post_id` = `posts`.`id`'; // post_shares

		$sql  .= ' LEFT JOIN `view_logs_embed` ON `view_logs_embed`.`post_id` = `posts`.`id`'; // view_logs_embed

		// Extend outer tables
		// Total embed for count average data
		$sql  .= ' LEFT OUTER JOIN (SELECT COUNT(*) cnt FROM `view_logs_embed`) ttl_embd ON `view_logs_embed`.`post_id` = `posts`.`id`';

		// Total shares
		$sql  .= ' LEFT OUTER JOIN (SELECT COUNT(*) cnt FROM `post_shares`) ttl_shrs ON `post_shares`.`post_id` = `posts`.`id`';

		$sql  .= ' LEFT OUTER JOIN (SELECT `view_logs_embed`.`post_id` embed_log_id, `posts`.`post_type`,COUNT(*) cnt';

		$sql  .= ' FROM `view_logs_embed`';

		$sql  .= ' LEFT JOIN `posts` ON `view_logs_embed`.`post_id` = `posts`.`id`';

		// Additional date range here
		if( $dateRange = $request->input('dateRange') )
		{ $sql  .= self::setDateRange($dateRange)->embed; }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ $sql  .= self::setDateRange(FALSE, $startDate, $endDate)->embed; }

		// dd( $sql );
		// $sql  .= ' WHERE FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';

		$sql  .= ' GROUP BY `posts`.`post_type`)';

		$sql  .= ' range_embed ON range_embed.post_type = `posts`.`post_type`';

		// Posts Outer
		$sql  .= ' LEFT OUTER JOIN (SELECT `posts`.`post_type`, COUNT(*) cnt, SUM(`posts`.`views`) cnt_views, AVG(`posts`.`views`) cnt_avg';
		$sql  .= ' FROM `posts`';

		// Additiopnal date range here
		if( $dateRange = $request->input('dateRange') )
		{ $sql  .= self::setDateRange($dateRange)->posts; }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ $sql  .= self::setDateRange(FALSE, $startDate, $endDate)->posts; }
		
		$sql  .= ' GROUP BY `posts`.`post_type`) range_posts'; 
		$sql  .= ' ON range_posts.post_type = `posts`.`post_type`';

		$sql  .= ' WHERE `posts`.`post_type` IN ('.self::$formatList.')';

		$sql  .= ' GROUP BY `posts`.`post_type`';

		// $sql  .= ' ORDER BY title ASC';

		// Sort
		if ($sort = $request->input('key'))
		{ 
			$sql .= self::setSort($sort, $request->input('reverse')); 
		// }else {
			// $sql .= self::setSort($sort, $request->input('reverse')); 
		}

		self::$formatData = DB::select($sql);

		return self::$__instance;
	}

	public static function cleanPaginate($take = 50) 
	{
		$paginate = [
			'data' 	=> self::$formatData,
			'total' => count(self::$formatData)
		];

		return $paginate;
	}

	private static function setSort($sortBy = 'created', $reverse = TRUE)
	{
		// dd( 'set' );
		$sql = null;
		$reverse = (!$reverse || ($reverse == 'false') ? 'ASC' : 'DESC');
		switch ($sortBy)
		{
			case 'post':
				$sql = 'ORDER BY total_posts '.$reverse.'';
				break;
			case 'view':
				$sql = 'ORDER BY total_views '.$reverse.'';
				break;
			case 'avg-view':
				$sql = 'ORDER BY average_views '.$reverse.'';
				break;
			case 'share':
				$sql = 'ORDER BY total_shares '.$reverse.'';
				break;
			case 'avg-share':
				$sql = 'ORDER BY average_shares '.$reverse.'';
				break;
			case 'embed':
				$sql = 'ORDER BY total_embed '.$reverse.'';
				break;
			case 'avg-embed':
				$sql = 'ORDER BY average_embed '.$reverse.'';
				break;
			default:
				$sql = 'ORDER BY title ASC';
				break;
		}

		return $sql;
	}

	private static function setDateRange($dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{
		$qryPosts = null;
		$qryEmbed = null;
		// // If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		if ($dateRange == 'all-time') { return (object)['embed' => $qryEmbed, 'posts' => $qryPosts]; }

		// ------------------------------------------------------------------------
		// Start Date and End Date are exist?
		if ($startDate AND $endDate)
		{
			$qryPosts = ' WHERE `posts`.`created_on` BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
			$qryEmbed = ' WHERE DATE(FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`)) BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).'"';
		}

		// // ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				$qryPosts = ' WHERE DATE(`posts`.`created_on`) = DATE(CURDATE())';
				$qryEmbed = ' WHERE DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = DATE(CURDATE())';
				break;
			case 'yesterday':
				$qryPosts = " WHERE DATE(`posts`.`created_on`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
				$qryEmbed = ' WHERE DATE(FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
				break;
			case 'last-7-days':
				$qryPosts = ' WHERE DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				$qryEmbed = ' WHERE DATE(FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				break;
			case 'last-30-days':
				$qryPosts = ' WHERE DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				$qryEmbed = ' WHERE DATE(FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				break;
			case 'last-90-days':
				$qryPosts = ' WHERE DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				$qryEmbed = ' WHERE DATE(FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				break;
			case 'this-month':
				$qryPosts = ' WHERE DATE_FORMAT(`posts`.`created_on`, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				$qryEmbed = ' WHERE DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				break;
			case 'this-year':
				$qryPosts = " WHERE YEAR(`posts`.`created_on`) = YEAR(CURDATE())";
				$qryEmbed = " WHERE YEAR(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = YEAR(CURDATE())";
				break;
		}

		// dd( $qryPosts );
		return (object)['posts' => $qryPosts, 'embed' => $qryEmbed];
	}

	/*==========================================
					RELATIONSHIP
	============================================*/
	public function formatPost()
	{
		$collection = $this->groupBy('post_type')->select('post_type');

		return $collection;
	}

	public function share()
	{ 
		$collection = $this->hasOne('App\Share', 'post_id');

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
$sql   = 'SELECT ts.`post_type` formats,';
		
// Post Views
$sql  .= ' FORMAT(SUM(ts.`views`), "de_DE") total_views,';
$sql  .= ' FORMAT(AVG(ts.`views`), "de_DE") average_views,';

// Post Shares 
$sql  .= ' COALESCE(SUM(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) total_shares,';
$sql  .= ' COALESCE(AVG(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) average_shares,';

// Post Embed 
$sql  .= ' COALESCE(range_embed.cnt, 0) total_embed,';
$sql  .= ' COALESCE( ( COALESCE(range_embed.cnt, 0) / COALESCE(ttl_embd.cnt, 0)), 0) average_embed';

// $sql  .= ' CONCAT(DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 7 DAY), "%d %M %Y"), " - ", DATE_FORMAT(CURDATE(), "%d %M %Y")) date_range';

// Main Table Posts
$sql  .= ' FROM posts ts';

// Join Table
$sql  .= ' LEFT JOIN `post_shares` ON `post_shares`.`post_id` = ts.`id`'; // post_shares

$sql  .= ' LEFT JOIN `view_logs_embed` ON `view_logs_embed`.`post_id` = ts.`id`'; // view_logs_embed

// Extend outer tables
$sql  .= ' LEFT OUTER JOIN (SELECT COUNT(*) cnt FROM `view_logs_embed`) ttl_embd ON `view_logs_embed`.`post_id` = ts.`id`';

$sql  .= ' LEFT OUTER JOIN (SELECT COUNT(*) cnt FROM `post_shares`) ttl_shrs ON `post_shares`.`post_id` = ts.`id`';

$sql  .= ' LEFT OUTER JOIN (SELECT `view_logs_embed`.`post_id` embed_log_id, COUNT(*) cnt';

$sql  .= ' FROM `view_logs_embed`';

$sql  .= ' LEFT JOIN `posts` ON `view_logs_embed`.`post_id` = `posts`.`id`';

// Embed total date range
// $sql  .= ' WHERE FROM_UNIXTIME(`view_logs_embed`.`lasT_activity`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';

$sql  .= ' GROUP BY `posts`.`post_type`,`view_logs_embed`.`post_id`)';

$sql  .= ' range_embed ON range_embed.embed_log_id = ts.`id`';

// $sql  .= ' WHERE ts.`post_type` IN ("article", "listicle", "funquiz", "gallery", "meme", "convo", "cardquiz", "quickpersonality", "quicktrivia", "quickpolling")';
$sql  .= ' WHERE ts.`post_type` IN ('.self::$formatList.')';

$sql  .= ' GROUP BY ts.`post_type`';

self::$formatData = DB::select($sql);
*/

/*=============
   ORI QUERY
===============*/
// self::$formatData = self::selectRaw('post_type, SUM(posts.views) as total_views, AVG(posts.views) as average_views');
// self::$formatData = self::selectRaw('post_type');
// self::$formatData   = self::$formatData->selectRaw('posts.post_type as title');
// self::$formatData   = self::$formatData->selectRaw('COUNT(*) as total_posts');
// self::$formatData   = self::$formatData->selectRaw('CAST(SUM(`posts`.`views`) as UNSIGNED) AS total_views');
// self::$formatData   = self::$formatData->selectRaw('CAST(AVG(`posts`.`views`) as UNSIGNED) AS average_views');
// self::$formatData   = self::$formatData->selectRaw('CAST(COALESCE(SUM(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) as UNSIGNED) AS total_shares');
// self::$formatData   = self::$formatData->selectRaw('CAST(COALESCE(AVG(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`), 0) as UNSIGNED) AS average_shares');
// self::$formatData   = self::$formatData->selectRaw('CAST(COUNT(`view_logs_embed`.`post_id`) as UNSIGNED) AS total_embed');
// // self::$formatData   = self::$formatData->selectRaw('COALESCE(range_embed.cnt, 0) total_embed');
// self::$formatData   = self::$formatData->selectRaw('CAST(COALESCE(AVG(`view_logs_embed`.`post_id`), 0) as UNSIGNED)AS average_embed');
// // self::$formatData   = self::$formatData->selectRaw('DATE_FORMAT(`posts`.`created_on`, "%d %M %Y %H:%i:%s") AS created_on');

// self::$formatData   = self::$formatData->leftJoin('post_shares', 'post_shares.id', '=', 'posts.id');
// self::$formatData   = self::$formatData->leftJoin('view_logs_embed', 'view_logs_embed.post_id', '=', 'posts.id');

// // Date range agregate
// self::$formatData   = self::$formatData->leftJoin(DB::raw('(SELECT `view_logs_embed`.`post_id` embed_log_id, COUNT(*) cnt FROM `view_logs_embed` LEFT JOIN `posts` ON `view_logs_embed`.`post_id` = `posts`.`id` GROUP BY `posts`.`post_type`, `view_logs_embed`.`post_id`) range_embed'), 'range_embed.embed_log_id', '=', 'posts.id');

// self::$formatData = self::$formatData->whereIn('post_type', self::$formatList);

// // self::$formatData = self::$formatData->distinct('range_embed.cnt');
// self::$formatData = self::$formatData->groupBy('post_type');

// // Date Range
// if ($dateRange = $request->input('dateRange'))
// { self::setDateRange($dateRange); }
// elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
// { self::setDateRange(FALSE, $startDate, $endDate); }