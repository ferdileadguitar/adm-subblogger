<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;

class AdminSettings extends \App\Http\Controllers\PageController
{

	public function getHome()
	{
		$data['user'] = empty(config('login.email')[0]) ? [] : config('login.email');

		return $this->view('settings', $data);
	}

	public function addAccessUser(Request $request)
	{
		// Validate duplicate
		$email     = trim($request->input('email'));
		$emailList = (is_null($request->input('emailList')) ? [] : $request->input('emailList'));

		// Block duplicate on current list
		if( in_array($email, config('login.email')) )
			die('Sorry dude email is exist');

		// Check is user have account on keepo
		$uData = \App\User::where('email', $email)->count(); // uData = user data

		if( $uData < 1 )
		{
			Session::flash('error', 'Make sure you have account on keepo dude');

			// dd(  )
			return redirect()->back();
			// die('Make sure you have account at keepo.me');
		}
		else
		{

			$storeData = array_merge($emailList, (array) $email);

			// Store add storage disk
			\Storage::disk('public')->put('login.txt', implode(",", $storeData));

			return redirect()->back();
			// die('This account avelaible');
		}
	}

	public function deletedAccessUser()
	{
		$key  = trim($this->request->input('key'));
		$list = config('login.email');
		// Delete array
		if( ($keys = array_search( $key, $list))  !== FALSE )
		{
			// Remove array
			unset($list[$keys]);
		}

		// Convert as string
		$list = implode(",", $list);

		// Update file storage
		\Storage::disk('public')->put('login.txt', $list);

		return redirect()->back();
	}
}
