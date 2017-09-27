<?php

use Illuminate\Http\Request;

// API
// ------------------------------------------------------------------------

Route::group(['middleware' => array('api')], function() {

	// Contents
	Route::get('contents', 'Api\ContentController@getContents');
	Route::get('contents/count-moderated', 'Api\ContentController@getCountModerated');

	Route::put('contents/set-status', 'Api\ContentController@setStatus');
	Route::put('contents/set-sticky', 'Api\ContentController@setSticky');
	Route::put('contents/set-premium', 'Api\ContentController@setPremium');

	Route::delete('contents', 'Api\ContentController@deleteContent');

	// Post or put feeds data from editor
	Route::match(['put', 'get'], 'feeds/{type?}', 'Api\ContentController@putFeed');

	// Image asset 
	Route::post('asset/cover-img', 'Api\AssetController@postImageCover');

	// Tags
	Route::get('tags', 'Api\ContentController@getTags');
});