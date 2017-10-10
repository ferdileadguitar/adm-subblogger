<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Channel;
use App\ObjectFile;
use App\Post;
use Carbon\Carbon;
use DB;
use Validator;

class Author extends Model
{
	public $timestamps  = false;

	protected $table    = 'users';
	protected $guarded  = [];

	protected $fillable = ['username', 'email','display_name', 'slug', 'activated'];

	private static $postList    = ['article', 'listicle', 'meme', 'gallery', 'funquiz', 'convo', 'quickpersonality', 'quicktrivia', 'quickpolling','cardclick'];
	private static $__instance  = null;
	private static $authorsData = false;

	private static $request     = false;

	// ------------------------------------------------------------------------
	// Public Methods
	// ------------------------------------------------------------------------

	public static function getInstance()
	{
		if (self::$__instance === null)
		{ self::$__instance = new self; }

		self::$request = Request();

		return self::$__instance;
	}

	// ------------------------------------------------------------------------
	
	public static function getFiltered($request = FALSE)
	{
		// Init
		// $postList = self::$postList;

		self::getInstance();

		self::$authorsData = self::with(['postShares']);
		self::$authorsData->selectRaw('`users`.`id`, `users`.`username`, `users`.`email`, `users`.`status`, `users`.`activated`');

		// ------------------------------------------------------------------------


		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }

		// Sort
		// dd( $request->input() );
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
		$paginate  = self::$authorsData->paginate($take)->toArray();

		$paginate['data'] = collect($paginate['data'])->map(function($item) {

			/*Count Date Response*/
			$total_posts   = collect($item['posts'])->count();
			$total_views   = collect($item['posts'])->sum('total_views');
			$total_embed   = collect($item['embed_log'])->count();
			$total_shares  = collect($item['post_shares'])->sum('total_shares');
			$average_views = @((is_null($total_views) || is_null($total_posts)) ? 0 : ($total_views / $total_posts));

			/*Build Object*/
			// $item['total_posts']   = (is_null($total_posts)   ? 0 : $total_posts);
			// $item['total_views']   = (is_null($total_views)   ? 0 : $total_views);
			// $item['average_views'] = (!$average_views)        ? 0 : $average_views;
			// $item['total_embed']   = (is_null($total_embed))  ? 0 : $total_embed;
			// $item['total_shares']  = (is_null($total_shares)) ? 0 : $total_shares;
			return [
				'id' 			=> @$item['id'],
				'username' 		=> @$item['username'],
				'status' 		=> @$item['status'],
				'activated' 	=> @$item['activated'],
				'email' 		=> @$item['email'],

				// Post Agregate 
				'total_posts'  	=> (is_null($total_posts)   ? 0 : $total_posts),
				'total_views'   => (is_null($total_views)   ? 0 : $total_views),
				'average_views' => (!$average_views)        ? 0 : $average_views,
				'total_embed'   => (is_null($total_embed))  ? 0 : $total_embed,
				'total_shares'  => (is_null($total_shares)) ? 0 : $total_shares,
			];
			// return $item;
		});	

		return $paginate;	
	}

	public static function countAuthor()
	{
		return self::whereIn('status', [1])->paginate(10);
	}

	private static function setSort($sortBy = 'created', $reverse = TRUE)
	{
		// dd( $sortBy );
		$reverse = (!$reverse || ($reverse == 'false') ? 'ASC' : 'DESC');
		switch ($sortBy)
		{
			case 'author':
				self::$authorsData->orderBy('users.username', $reverse); 
				break;
			case 'post':
				self::$authorsData->orderBy('post_type', $reverse);
				break;
			case 'avg-view':
				self::$authorsData->orderBy('view', $reverse);
				break;
			case 'total-share':
				self::$authorsData
					 // ->selectRaw('`posts`.*, (SELECT `post_shares`.`shares` FROM `post_shares` WHERE `post_shares`.`post_id` = `posts`.`id`) as `share_count`')
					 ->orderBy('share_count', $reverse);
				break;
			case 'embed':
				self::$authorsData
					 ->selectRaw('(SELECT COUNT(`view_logs_embed`.`post_id`) FROM `view_logs_embed` WHERE `view_logs_embed`.`post_id` = `posts`.`id`) as `embed_count`')
					 ->orderBy('embed_count', $reverse);
				break;
			case 'email':
				self::$authorsData
					 // ->selectRaw('`posts`.*, (SELECT COUNT(`post_embed`.`id_embed`) FROM `post_embed` WHERE `post_embed`.`id_post` = `posts`.`id`) as `embed_count`')
					 ->orderBy('email', $reverse);
				break;
			case 'created':
			default:
				self::$authorsData->orderBy('users.created_on', $reverse);
				break;
		}
	}

	private static function setTotalViews()
	{

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

		self::$authorsData->with([
			'posts' => function($query) use ($dateRange, $qryPosts){
				if(!is_null($qryPosts))
					$query->whereRaw($qryPosts);

				$query->select('user_id', 'id', DB::raw('CAST(SUM(`posts`.`views`) as UNSIGNED) as total_views'));
				$query->groupBy('posts.user_id', 'posts.id');
			},
			'embedLog' => function($query) use ($dateRange, $qryEmbed)
			{ if(!is_null($qryEmbed)) $query->whereRaw($qryEmbed); }
		]);
	}

	private static function setSearch($search = FALSE)
	{
		self::$authorsData->where(function($query) use ($search) {
			// $query->whereRaw('MATCH(posts.title) AGAINST ("' . $search . '")');
			$query->whereRaw('users.username LIKE "%' . $search . '%" OR users.email = "' . $search . '"');
		});
	}

	public static function setActivated($usersID = array())
	{
		self::$authorsData->whereIn(function($query) use ($usersID) {
			$query->whereIn('id', $usersID);
			$query->update(['activated' => 1]);
		});
	}

	public static function setStatus($usersID)
	{
		if( !self::whereIn('id', [$usersID])->update(['users.status' => -99]) )
			 return ['error' => array('error_description' => 'Failed to delete')];

		return ['status' => 'ok', 'total' => self::countAuthor()->total()];
	}

	public static function resetPassword($userID, $password)
	{	
		if( empty($userID) )
		{ return ['error' => 'UserID not found :(']; }

		// Validate request
        $validator = Validator::make(array('password' => $password), array(
            'password'     => 'required|between:6,11',
        ));

        // Is it valid?
        if(! $validator->fails())
        {
            // Prepare data
            $salt = createSalt();
            $data = array(
                'password' => cryptPass($salt, $password),
                'salt'     => $salt
            );

            // Lets update data
            if(!self::where('id', $userID)->update($data))
                return ['error' => array('error_description' => 'Failed to update password')];

        }
        else
        	return ['error' => array('error_description' => $validator->errors()->first())];
		
        return ['status' => 'ok'];
	}

	public function posts()
	{
		return $this->hasMany('App\Post', 'user_id')->whereIn('post_type', self::$postList)->select('user_id');
	}

	// --------------------- POST AGREGATE ---------------------------------------------------------

	/**
	  * This one, for agregate embed Log and seperate from the Date Range
	  @params null
	  @return collection
	**/
	public function embedLog(){
		$collection = $this->hasManyThrough('App\EmbedLog', 'App\Post', 'user_id');

		$collection = $collection->selectRaw('`view_logs_embed`.`user_id`, `view_logs_embed`.`post_id`, `view_logs_embed`.`shareid`, `view_logs_embed`.`last_activity`, DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m-%d %H:%i:%s") as created_on');
		
		return $collection;
	}

	/**
	  * This one, for agregate embed Log and seperate from the Date Range
	  * We also can't filtering with date range because it has only one row for one post(s)
	  @params null
	  @return collection
	**/
	public function postShares(){
		$collection = $this->posts();

		$collection = $collection->join('post_shares', 'posts.id', 'post_shares.post_id');

		$collection = $collection->selectRaw('`posts`.`user_id`, `post_shares`.`post_id`, `post_shares`.`fb`, `post_shares`.`twitter`, `post_shares`.`shares`, CAST(SUM(`post_Shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`) as UNSIGNED) as total_shares');

		$collection = $collection->groupBy('post_id', 'user_id' ,'fb', 'twitter', 'addon', 'shares');
		
		return $collection;
	}

	/**
	  * This one, for agregate views posts
	  @params null
	  @return collection
	**/
	public function postsSumViews()
	{
		return $this->posts()->select(DB::raw('user_id, CAST(SUM(`views`) AS UNSIGNED)AS "total_views"'))->groupBy('user_id');
	}
}

/*FIRST ATTEMPT*/
// $sql     = "SELECT `users`.`username`, ";

/*$sql     = "SELECT *, COUNT(*) AS 'Total Post', ";

$sql    .= "CAST(SUM(`posts`.`views`) as UNSIGNED) AS 'Total View', ";

$sql    .= "AVG(`posts`.`views`) AS 'Average View', ";

$sql    .= "CAST(SUM(`post_shares`.`fb` + `post_shares`.`shares` + `post_shares`.`twitter` + `post_shares`.`addon`) as UNSIGNED) AS 'Total Share', ";

$sql    .= "(SUM(`post_embed`.`view`)) AS 'Total Embed' ";

$sql    .= "FROM `users` "; 

$sql    .= "LEFT JOIN `posts` ON `posts`.`user_id` = `users`.`id` ";
					
$sql    .= "LEFT JOIN `post_shares` ON `posts`.`id` = `post_shares`.`post_id` ";
					
$sql    .= "LEFT JOIN `post_embed` ON `posts`.`id` = `post_embed`.`id_post` ";
					
$sql    .= "GROUP BY `users`.`id`";

$sql  = DB::select($sql);
*/
// $data = collect($sql)->map(function($item){ return (array) $item; })->toArray();

// $paginate['data'] = collect($paginate['data'])->map(function($item) {
// 	// dd( @$item );
// 	return [
// 		'id' 	    	=> $item['id'], 
// 		'username' 		=> $item['username'], 
// 		'display_name' 	=> $item['display_name'],
// 		'email'         => $item['email'],
// 		'status' 		=> $item['status'],
// 		'activated'		=> $item['activated'],

// 		// Total Post related users
// 		// 'posts'         => array(
// 		'total_posts' 	 => (int)@$item['posts_sum_views'][0]['total_views'],
// 		'total_views' 	 => (int)@$item['posts_count'][0]['total_posts'],
// 		'average_views'  => @$item['posts_average'][0]['average_posts']
// 	];	

// });

/*SECOND ATTEMPT*/
// self::$authorsData->selectRaw('COALESCE(AVG(`posts`.`views`), 0) AS "average_views"');

// self::$authorsData->selectRaw('COALESCE(CAST(SUM(`post_shares`.`fb` + `post_shares`.`shares` + `post_shares`.`twitter` + `post_shares`.`addon`) as UNSIGNED), 0) AS "total_shares"');

// self::$authorsData->selectRaw('COALESCE(CAST(SUM(`post_embed`.`view`) as UNSIGNED), 0) AS "total_embed"');

// self::$authorsData->leftJoin('posts', 'users.id', '=', 'posts.user_id');

// self::$authorsData->leftJoin('post_shares', 'posts.id', '=', 'post_shares.post_id');

// self::$authorsData->leftJoin('post_embed', 'posts.id', '=', 'post_embed.id_post'); 

// self::$authorsData->whereIn('users.status', [1]); 

// // self::$authorsData->whereIn('posts.post_type', self::$postList); 

// self::$authorsData->groupBy('users.id', 'users.username', 'users.email', 'users.status', 'users.activated');

// dd( self::$authorsData->toSql() );