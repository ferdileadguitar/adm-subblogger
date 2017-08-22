<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

// API
// ------------------------------------------------------------------------

Route::group(array('prefix' => 'api'), function() {

	// Contents
	Route::get('contents', 'Api\ContentController@getContents');
	Route::get('contents/count-moderated', 'Api\ContentController@getCountModerated');

	Route::put('contents/set-status', 'Api\ContentController@setStatus');
	Route::put('contents/set-sticky', 'Api\ContentController@setSticky');
	Route::put('contents/set-premium', 'Api\ContentController@setPremium');

	Route::delete('contents', 'Api\ContentController@deleteContent');

	// Post or put feeds data from editor
	Route::match(['put', 'get', 'post'], 'feeds', 'Api\ContentController@putFeed');

	// Image asset 
	Route::post('asset/cover-img', 'Api\AssetController@postImageCover');

	// Tags
	Route::get('tags', 'Api\ContentController@getTags');
});

// Pages
// ------------------------------------------------------------------------

// Contents
Route::get('/', 'Pages\ContentController@index')->name('content');
Route::get('/contents', 'Pages\ContentController@index')->name('contentWithURI');


// Login - Logout
// ------------------------------------------------------------------------

Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
Route::post('/login', 'Auth\LoginController@tryLogin')->name('login');
