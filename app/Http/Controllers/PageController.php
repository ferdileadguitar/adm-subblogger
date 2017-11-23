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

        $this->slug    = (in_array($this->request->segment(1), [null, 'users'])) ? 'contents' : $this->request->segment(1);

    	$this->globalData = [
    		'pageTitle' => title_case($this->slug) .' - ' . config('app.name'),
    		'activeNav' => $this->slug,
            'adminUser' => json_encode($this->request->session()->get('admin:username'), TRUE)
            // 'adminUser' => 'anonymous'
    	];

        $this->adminUser = $this->request->session()->get('admin:username');
    }

    // ------------------------------------------------------------------------
    
    protected function view($page, $data = [])
    { return view('pages.' . $page, array_merge($this->globalData, $data)); }
}
