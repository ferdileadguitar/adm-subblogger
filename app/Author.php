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

	protected static $postDateRange   = null;
	protected static $embedDateRange  = null;

	private static $__instance  	  = null;
	private static $authorsData 	  = false;

	private static $request           = false;

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
		self::getInstance();

		self::$authorsData = self::with(['postShares']);
		self::$authorsData->selectRaw('`users`.`id`, `users`.`username`, `users`.`email`, `users`.`status`, `users`.`activated`');
		self::$authorsData->where('status', 1);

		// ------------------------------------------------------------------------

		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }

		// Grouping author by default sort
		self::groupSort();
		
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
		$paginate  = self::$authorsData->paginate($take)->toArray();

		$paginate['data'] = collect($paginate['data'])->map(function($item) {
			/*Count Date Response*/
			// $total_posts   = collect($item['posts'])->count();
			// $total_views   = collect($item['posts'])->sum('total_views');
			// $total_embed   = collect($item['embed_log'])->sum('cnt_embed');
			$total_shares  = collect($item['post_shares'])->sum('total_shares');
			// $average_views = @((empty($total_views) || empty($total_posts)) ? 0 : ($total_views / $total_posts));

			/*Build Object*/
			return [
				'id' 			=> @$item['id'],
				'username' 		=> @$item['username'],
				'status' 		=> @$item['status'],
				'activated' 	=> @$item['activated'],
				'email' 		=> @$item['email'],

				// Post Agregate 
				// 'total_posts'  	=> (is_null($total_posts)   ? 0 : $total_posts),
				'total_posts'  	=> @$item['total_posts'],
				// 'total_views'   => (is_null($total_views)   ? 0 : $total_views),
				'total_views'   => @$item['total_views'],
				// 'average_views' => (!$average_views)        ? 0 : $average_views,
				'average_views' => @$item['avg_posts'],
				// 'total_embed'   => (is_null($total_embed))  ? 0 : $total_embed,
				'total_embed'   => @$item['embed_count'],
				'total_shares'  => (is_null($total_shares)) ? 0 : $total_shares,
			];
		});

		// dd( $paginate );
		return $paginate;	
	}

	public static function countAuthor()
	{
		return self::whereIn('status', [1])->paginate(10);
	}

	private static function groupSort() 
	{
		// Total Posts
		$sql  = '`users`.`id`, `users`.`username`, `users`.`email`, `users`.`activated`, `users`.`status`, (SELECT COUNT(*) FROM `posts`';

		$sql .= ' JOIN `channels` ON `channels`.`id` = `posts`.`channel_id`';

		$sql .= ' WHERE `posts`.`user_id` = `users`.`id`';
		
		$sql .= ' AND `channels`.`slug` IN ("'.implode(config('list.channel'), '","').'")';

		$sql .= ' AND `posts`.`post_type` IN ("'.implode(config('list.post_type'), '","').'")';
		
		$sql .= is_null(self::$postDateRange) ? null : ' AND '.self::$postDateRange;
		
		$sql .= ') total_posts,';

		// Total views
		$sql .= '`users`.`id`, `users`.`username`, `users`.`email`, `users`.`activated`, `users`.`status`, (SELECT CAST(COALESCE(SUM(`posts`.`views`), 0) as UNSIGNED)';

		$sql .= ' FROM `posts`';
		
		$sql .= ' JOIN `channels` ON `channels`.`id` = `posts`.`channel_id`';

		$sql .= ' WHERE `posts`.`user_id` = `users`.`id`';
		
		$sql .= ' AND `posts`.`post_type` IN ("'.implode(config('list.post_type'), '","').'")';
		
		$sql .= ' AND `channels`.`slug` IN ("'.implode(config('list.channel'), '","').'")';

		$sql .= is_null(self::$postDateRange) ? null : ' AND '.self::$postDateRange;
		
		$sql .= ') total_views,';

		// AVG Views
		$sql .= '`users`.`id`, `users`.`username`, `users`.`email`, `users`.`activated`, `users`.`status`, (SELECT COALESCE(AVG(`posts`.`views`), 0)';

		$sql .= ' FROM `posts`';
		
		$sql .= ' JOIN `channels` ON `channels`.`id` = `posts`.`channel_id`';

		$sql .= ' WHERE `posts`.`user_id` = `users`.`id`';
		
		$sql .= ' AND `posts`.`post_type` IN ("'.implode(config('list.post_type'), '","').'")';
		
		$sql .= ' AND `channels`.`slug` IN ("'.implode(config('list.channel'), '","').'")';

		$sql .= is_null(self::$postDateRange) ? null : ' AND '.self::$postDateRange;
		
		$sql .= ') avg_posts,';

		// Embed
		$sql .= '`users`.`id`, `users`.`username`, `users`.`email`, `users`.`activated`, `users`.`status`, (SELECT COUNT(*) cnt FROM `view_logs_embed` ';
		
		$sql .= 'WHERE `users`.`id` = `view_logs_embed`.`user_id`';
		
		$sql .= is_null(self::$embedDateRange) ? null : ' AND '.self::$embedDateRange;
		
		$sql .=') embed_count';
		
		self::$authorsData->selectRaw($sql);
	}

	private static function setSort($sortBy = 'created', $reverse = TRUE)
	{
		$reverse = (!$reverse || ($reverse == 'false') ? 'ASC' : 'DESC');

		switch ($sortBy)
		{
			case 'post':
				self::$authorsData->orderBy('total_posts', $reverse);
				break;
			case 'view':
				self::$authorsData->orderBy('total_views', $reverse);
				break;
			case 'avg-view':
				self::$authorsData->orderBy('avg_posts', $reverse);
				break;
			case 'share':
				self::$authorsData
					->selectRaw('`users`.*, (SELECT SUM(`post_shares`.`addon` + `post_shares`.`shares`) FROM `posts` LEFT JOIN `post_shares` ON `posts`.`id` = `post_shares`.`post_id` WHERE `users`.`id` = `posts`.`user_id`) as `share_count`')
					->orderBy('share_count', $reverse);
				break;
			case 'embed':
				self::$authorsData->orderBy('embed_count', $reverse);
				break;
			default:
				self::$authorsData->orderBy('users.created_on', 'ASC');
				break;
		}
	}

	private static function setDateRange($dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{

		// If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		if ($dateRange == 'all-time') { self::$embedDateRange;self::$postDateRange; }

		// ------------------------------------------------------------------------
		
		// Start Date and End Date are exist?
		if ($startDate AND $endDate)
		{
			self::$postDateRange  = '`posts`.`created_on` BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
			self::$embedDateRange = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"';
		}

		// ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				self::$postDateRange    = 'DATE(`posts`.`created_on`) = DATE(CURDATE())';
				self::$embedDateRange   = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = DATE(CURDATE())';
				break;
			case 'yesterday':
				self::$postDateRange    = 'DATE(`posts`.`created_on`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
				self::$embedDateRange   = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)';
				break;
			case 'last-7-days':
				self::$postDateRange    = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				self::$embedDateRange   = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
				break;
			case 'last-30-days':
				self::$postDateRange    = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				self::$embedDateRange   = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
				break;
			case 'last-90-days':
				self::$postDateRange    = 'DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				self::$embedDateRange   = 'DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)';
				break;
			case 'this-month':
				self::$postDateRange    = 'DATE_FORMAT(`posts`.`created_on`, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				self::$embedDateRange   = 'DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")';
				break;
			case 'this-year':
				self::$postDateRange    = 'YEAR(`posts`.`created_on`) = YEAR(CURDATE())';
				self::$embedDateRange   = 'YEAR(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = YEAR(CURDATE())';
				break;
		}

		self::$authorsData->with([
			'posts' => function($query) use ($dateRange){
				if(!is_null(self::$postDateRange))
					$query->whereRaw(self::$postDateRange);

				$query->select('user_id', 'id', DB::raw('CAST(SUM(`posts`.`views`) as UNSIGNED) as total_views'));
				$query->groupBy('posts.user_id', 'posts.id');
			},
			'embedLog' 
			// => function($query)
			// { 
			// 	if(!is_null(self::$embedDateRange)) 
			// 		$query->whereRaw(self::$embedDateRange); 

			// 	$query->selectRaw('COUNT(*) cnt_embed');
			// 	// $query->groupBy('view_logs_embed.user_id');
			// }
		]);
	}

	private static function setSearch($search = FALSE)
	{
		self::$authorsData->where(function($query) use ($search) {
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
		return $this->hasMany('App\Post', 'user_id')->whereIn('post_type', config('list.post_type'))->select('user_id');
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

		$collection = $collection->selectRaw('`posts`.`user_id`, `post_shares`.`post_id`, `post_shares`.`fb`, `post_shares`.`twitter`, `post_shares`.`shares`, CAST(SUM(`post_shares`.`fb` + `post_shares`.`twitter` + `post_shares`.`addon` + `post_shares`.`shares`) as UNSIGNED) as total_shares');

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

// // self::$authorsData->whereIn('posts.post_type', config('list.post_type')); 

// self::$authorsData->groupBy('users.id', 'users.username', 'users.email', 'users.status', 'users.activated');

// dd( self::$authorsData->toSql() );