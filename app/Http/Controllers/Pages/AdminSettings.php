<?php

namespace App\Http\Controllers\Pages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;

class AdminSettings extends \App\Http\Controllers\PageController
{

	public function getHome()
	{
		$data['user']         = config('login.email');
		$data['contributor']  = \App\User::whereIn('id', config('list.contributor'))->select('id', 'email')->get()->toArray();

		return $this->view('settings', $data);
	}

	public function addAccessUser(Request $request)
	{
		// Validate duplicate
		$email     = trim($request->input('email'));
		$emailList = (is_null($request->input('emailList')) ? [] : $request->input('emailList'));

		// Block duplicate on current list
		if( in_array($email, config('login.email')) )
		{
			return redirect('settings')->with(['user:flash' => 'danger', 'msg' => 'Email was exists!']);
		
		}

		// Check is user have account on keepo
		$uData = \App\User::where('email', $email)->count(); // uData = user data

		if( $uData < 1 )
		{
			return redirect('settings')->with(['user:flash' => 'danger', 'msg' => 'Make sure you have account on keepo dude']);
		}
		else
		{

			$storeData = array_merge($emailList, (array) $email);

			// Store add storage disk
			\Storage::disk('public')->put('login.txt', implode(",", $storeData));

			return redirect('settings')->with(['user:flash' => 'success', 'msg' => 'Account has been added']);
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

		return redirect('settings')->with(['user:flash' => 'success', 'msg' => 'Account has been deleted']);
	}

	public function addContributorUser(Request $request)
	{
		// Validate duplicate
		$email     = trim($request->input('email'));
		$emailList = config('list.contributor');

		// Check is user have account on keepo
		$uData = \App\User::where('email', $email); // uData = user data

		if( $uData->count() < 1 )
		{
			return redirect('settings')->with(['contributor:flash' => 'danger', 'msg' => 'Make sure you have account on keepo dude']);
		}
		else
		{
			// email on list
			if( in_array($uData->first()->id, $emailList) ) { 
				return redirect('settings')->with(['contributor:flash' => 'danger', 'msg' => 'Email was exists!']);
			}

			$userID = $uData->first()->id; 
			
			$storeData = array_merge($emailList, (array) $userID);

			\Storage::disk('public')->put('contributor.txt', implode(",", $storeData));

			return redirect('settings')->with(['contributor:flash' => 'success', 'msg' => 'Account has been added']);
		}
	}

	public function deletedContributorUser()
	{
		$userID    = trim($this->request->input('key'));
		$emailList = config('list.contributor');

		// Delete array
		if( ($keys = array_search($userID, $emailList)) !== FALSE )
			unset($emailList[$keys]);

		// Convert to string
		$list = implode(",", $emailList);

		// Update file storage
		\Storage::disk('public')->put('contributor.txt', $list);

		return redirect('settings')->with(['contributor:flash' => 'success', 'msg' => 'Account has been deleted']);
	}
}
