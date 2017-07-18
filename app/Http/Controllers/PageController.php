<?php
namespace App\Http\Controllers;

class PageController extends Controller
{
    public function __construct()
    { $this->middleware('auth'); }

    // ------------------------------------------------------------------------
    
    protected function view($page, $data = [])
    { return view('pages.' . $page, $data); }
}
