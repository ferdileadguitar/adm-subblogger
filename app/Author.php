<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Channel;
use App\objectFile;
use Carbon\Carbon;
use DB;

class Author extends Model
{
	public $timestamps  = false;

	protected $table    = 'users';
	protected $guarded  = [];

	protected $fillable = ['username', 'email', 'salt', 'password', 'birthday', 'display_name', 'slug', 'region', 'about_me', 'birthday', 'sex', 'cards', 'object_file_id', 'created_ip', 'activated'];
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
		self::getInstance();
		self::$authorsData = self::with('postsSumViews');


		// ------------------------------------------------------------------------
		

		// Date Range
		if ($dateRange = $request->input('dateRange'))
		{ self::setDateRange($dateRange); }
		elseif (($startDate = $request->input('startDate')) AND ($endDate = $request->input('endDate')))
		{ self::setDateRange(FALSE, $startDate, $endDate); }

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
		$paginate         = self::$authorsData->paginate($take)->toArray();

		dd( $paginate );

		$paginate['data'] = collect($paginate['data'])->map(function($item) {
			// dd( count($item['posts']) );
			return [
				'id' 	    	=> $item['id'], 
				'username' 		=> $item['username'], 
				'display_name' 	=> $item['display_name'],
				'email'         => $item['email'],
				'status' 		=> $item['status'],
				'activated'		=> $item['activated'],

				// Total Post related users
				'posts'         => count($item['posts'])
			];	

		});
		
		dd( $paginate );
		return $paginate;	
	}

	private static function setDateRange($dateRange = 'all-time', $startDate = FALSE, $endDate = FALSE)
	{
		// If dateRange is 'all-time', well dont filter the date then ¯\_(ツ)_/¯
		if ($dateRange == 'all-time') { return; }

		// ------------------------------------------------------------------------
		
		// Start Date and End Date are exist?
		if ($startDate AND $endDate)
		{
			self::$authorsData->where(function($query) use($startDate, $endDate) {
				$query->whereBetween('created_on', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
			});
			return;
		}

		// ------------------------------------------------------------------------
		
		switch ($dateRange) 
		{
			case 'today':
				self::$authorsData->whereRaw("DATE(posts.created_on) = DATE(CURDATE())");
				break;
			case 'yesterday':
				self::$authorsData->whereRaw("DATE(posts.created_on) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
				break;
			case 'last-7-days':
				self::$authorsData->whereRaw('DATE(posts.created_on) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
				break;
			case 'last-30-days':
				self::$authorsData->whereRaw('DATE(posts.created_on) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
				break;
			case 'last-90-days':
				self::$authorsData->whereRaw('DATE(posts.created_on) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)');
				break;
			case 'this-month':
				self::$authorsData->whereRaw('DATE_FORMAT(posts.created_on, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m")');
				break;
			case 'this-year':
				self::$authorsData->whereRaw("YEAR(posts.created_on) = YEAR(CURDATE())");
				break;
		}
	}

	private static function setSearch($search = FALSE)
	{
		self::$authorsData->where(function($query) use ($search) {
			$query->whereRaw('MATCH(posts.title) AGAINST ("' . $search . '")');
		});
	}

	public function posts()
	{
		return $this->hasMany('App\Post', 'user_id');
	}

	public function postsSumViews()
	{
		return $this->posts()->selectRaw('COUNT(*) AS "total_views"')->groupBy('user_id');
	}

	public function postsCount()
	{
		return $this->posts()->selectRaw('count(*) as "total_post"')->groupBy('user_id');
	}
}