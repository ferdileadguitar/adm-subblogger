<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{	

	public function __construct(Request $request) {
		$this->request = $request;
	}

    protected function response($data = [], $status = 200)
    {
    	return response()->json($data, $status);
    }

     /**
     * Standard JSON response
     * @param array $json 
     * @return object
     */
    final protected function giveJson($json)
    {
        return response()->json($json);
    }
    
    /**
     * Standard JSON success response
     * @param string $description 
     * @return object
     */
    final protected function giveDesc($description = '', $addon = array())
    {
        return response()->json(array_merge(array('description' => $description), $addon))->send();
    }
    
     /**
     * Standard JSON abort
     * @param integer $status 
     * @param string $category 
     * @param string $description 
     * @return object
     */
    final protected function abortRequest($status, $category = 'access_denied', $description = '')
    {
        return response()->json(array('error' => $category, 'error_description' => $description), $status);
    }
}
