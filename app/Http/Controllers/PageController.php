<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request; 

class PageController extends Controller
{
	public $request;
	public $globalData;

    public function __construct()
    { 

    	$this->request = Request();

        $this->slug    = is_null($this->request->segment(1)) ? 'contents' : $this->request->segment(1);

    	$this->globalData = [
    		'pageTitle' => title_case($this->slug) .' - ' . config('app.name'),
    		'activeNav' => $this->slug,
            'adminUser' => json_encode($this->request->session()->get('admin:username'), TRUE)
    	];
        // dd( $this->globalData );
        $this->adminUser = $this->request->session()->get('admin:username');

        // dd( $this );
        // dd( $this->request->segment(1) );
    }

    // ------------------------------------------------------------------------
    
    protected function view($page, $data = [])
    { return view('pages.' . $page, array_merge($this->globalData, $data)); }
}
