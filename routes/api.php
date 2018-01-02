<?php

use Illuminate\Http\Request;

// API
// ------------------------------------------------------------------------

Route::group(['middleware' => array('api', 'admin')], function() {

	// Contents
	Route::get('contents', 'Api\ContentController@getContents');

	Route::put('contents/set-status', 'Api\ContentController@setStatus');
	Route::put('contents/set-sticky', 'Api\ContentController@setSticky');
	Route::put('contents/set-premium', 'Api\ContentController@setPremium');

	Route::delete('contents', 'Api\ContentController@deleteContent');

	// Post or put feeds data from editor
	Route::match(['put', 'get'], 'feeds/{type?}', 'Api\ContentController@feedState');

	// Authors
	Route::get('authors', 'Api\AuthorsController@getAuthors');
	Route::match(['put', 'post', 'delete'], 'authors/{type}', 'Api\AuthorsController@authorState');

	// Channel
	Route::get('channels', 'Api\ChannelController@getChannel');

	// Formats
	Route::get('formats', 'Api\FormatController@getFormat');

	// Image asset 
	Route::post('asset/cover-img', 'Api\AssetController@postImageCover');

	// Tags
	Route::get('tags', 'Api\ContentController@getTags');
});

// Login
Route::post('login', ['middleware' => 'api', 'uses' => 'Api\LoginController@tryLogin']);
Route::post('logout', ['middleware' => 'api', 'uses' => 'Api\LoginController@tryLogout']);