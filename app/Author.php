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

	private static $postsSubQry       = false;
	private static $shareSubQry       = false;
	private static $embedSubQry       = false;

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

		self::$authorsData = new self();
		
		self::$postsSubQry = $postsQry  = DB::table('posts')->selectRaw('`posts`.`id`, `posts`.`user_id`, COUNT(*) cnt, sum(`views`) sum, avg(`views`) avg')->groupBy('posts.user_id')->whereRaw('`posts`.`status` not in (-99, -1)');

		self::$shareSubQry = $sharesQry = DB::table('post_shares')->selectRaw('`posts`.`user_id`, `post_shares`.`post_id`, SUM(`post_shares`.`shares` + `post_shares`.`addon`) cnt')->join('posts', 'posts.id', '=', 'post_shares.post_id')->groupBy('posts.user_id');
		
		self::$embedSubQry = $embedsQry = DB::table('view_logs_embed')->selectRaw('`view_logs_embed`.`user_id`, `view_logs_embed`.`last_activity`, COUNT(*) cnt')->groupBy('view_logs_embed.user_id');

		self::$authorsData = self::$authorsData->selectRaw('`users`.`id`, `users`.`username`, `users`.`email`, `users`.`status`, `users`.`activated`');

		self::$authorsData = self::$authorsData->selectRaw('COALESCE(range_posts.cnt, 0) total_posts');

		self::$authorsData = self::$authorsData->selectRaw('CAST(COALESCE(range_posts.sum, 0) as UNSIGNED) total_views');

		self::$authorsData = self::$authorsData->selectRaw('COALESCE(CONVERT(range_posts.avg, DECIMAL(7,2)), 0) average_views');

		self::$authorsData = self::$authorsData->selectRaw('COALESCE(range_shares.cnt, 0) total_shares');

		self::$authorsData = self::$authorsData->selectRaw('COALESCE(range_embeds.cnt, 0) total_embed');
		
		// ------------------------------------------------------------------------

		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }

		self::$authorsData = self::$authorsData->leftJoin(DB::raw("({$postsQry->toSql()}) range_posts"), 'range_posts.user_id', '=', 'users.id');
		
		self::$authorsData = self::$authorsData->leftJoin(DB::raw("({$sharesQry->toSql()}) range_shares"), 'range_shares.user_id', '=', 'users.id');

		self::$authorsData = self::$authorsData->leftJoin(DB::raw("({$embedsQry->toSql()}) range_embeds"), 'range_embeds.user_id', '=', 'users.id');

		self::$authorsData = self::$authorsData->where('users.status', 1);
		
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

		return $paginate;	
	}

	public static function countAuthor()
	{
		return self::whereIn('status', [1])->paginate(10);
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
				self::$authorsData->orderBy('average_views', $reverse);
				break;
			case 'share':
				self::$authorsData->orderBy('total_shares', $reverse);
				break;
			case 'embed':
				self::$authorsData->orderBy('total_embed', $reverse);
				break;
			default:
				self::$authorsData->orderBy('users.created_on', 'ASC');
				break;
		}
	}

	private static function setDateRange($dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{

		// If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		if ($dateRange == 'all-time') { return; }

		// ------------------------------------------------------------------------
		
		// Start Date and End Date are exist?
		if ($startDate AND $endDate)
		{
			self::$postsSubQry      = self::$postsSubQry->whereRaw('`posts`.`created_on` BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).' 23:59:59"');
			self::$embedSubQry      = self::$embedSubQry->whereRaw('DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) BETWEEN "'.date('Y-m-d', strtotime($startDate)).' 00:00:00" AND "'.date('Y-m-d', strtotime($endDate)).'"');
		}

		// ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				self::$postsSubQry      = self::$postsSubQry->whereRaw('DATE(`posts`.`created_on`) = DATE(CURDATE())');
				self::$embedSubQry      = self::$embedSubQry->whereRaw('DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = DATE(CURDATE())');
				break;
			case 'yesterday':
				self::$postsSubQry      = self::$postsSubQry->whereRaw('DATE(`posts`.`created_on`) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)');
				self::$embedSubQry      = self::$embedSubQry->whereRaw('DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)');
				break;
			case 'last-7-days':
				self::$postsSubQry      = self::$postsSubQry->whereRaw('DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
				self::$embedSubQry      = self::$embedSubQry->whereRaw('DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
				break;
			case 'last-30-days':
				self::$postsSubQry      = self::$postsSubQry->whereRaw('DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
				self::$embedSubQry      = self::$embedSubQry->whereRaw('DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
				break;
			case 'last-90-days':
				self::$postsSubQry      = self::$postsSubQry->whereRaw('DATE(`posts`.`created_on`) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)');
				self::$embedSubQry      = self::$embedSubQry->whereRaw('DATE(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)');
				break;
			case 'this-month':
				self::$postsSubQry      = self::$postsSubQry->whereRaw('DATE_FORMAT(`posts`.`created_on`, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")');
				self::$embedSubQry      = self::$embedSubQry->whereRaw('DATE_FORMAT(FROM_UNIXTIME(`view_logs_embed`.`last_activity`), "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")');
				break;
			case 'this-year':
				self::$postsSubQry      = self::$postsSubQry->whereRaw('YEAR(`posts`.`created_on`) = YEAR(CURDATE())');
				self::$embedSubQry      = self::$embedSubQry->whereRaw('YEAR(FROM_UNIXTIME(`view_logs_embed`.`last_activity`)) = YEAR(CURDATE())');
				break;
		}

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
            'password'     => 'required|between:6,20',
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
		$collection = $this->hasMany('App\Post', 'user_id');

		$collection = $collection->join('channels', 'channels.id', '=', 'posts.channel_id');
		
		$collection = $collection->whereIn('post_type', config('list.post_type'));

		$collection = $collection->whereIn('channels.slug', config('list.channel'));

		$collection = $collection->whereNotIn('posts.status', [-99]);

		// dd( $collection->toSql() );
		return $collection->select('posts.user_id', 'posts.post_type', 'posts.views', 'channels.slug');
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