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

    	$this->globalData = [
    		'pageTitle' => 'Contents - ' . config('app.name'),
    		'activeNav' => 'contents'
    	];
    }

    // ------------------------------------------------------------------------
    
    protected function view($page, $data = [])
    { return view('pages.' . $page, array_merge($this->globalData, $data)); }
}
