<?php

Auth::routes();

// Admin Pages
// ------------------------------------------------------------------------
Route::group(['middleware' => 'admin'], function() {

	// Contents
	Route::get('/', 'Pages\ContentController@index')->name('content');
	Route::get('/contents', 'Pages\ContentController@index')->name('contentWithURI');
	Route::get('users/{username}', 'Pages\ContentController@getPost')->name('contentWithUser');

	// Author
	Route::get('authors', 'Pages\AuthorController@index')->name('authorWithURI');

	// Channel & Formats
	Route::get('channels', 'Pages\ChannelController@index')->name('channelWithURI');
});

// Login - Logout
// ------------------------------------------------------------------------
Route::group(['middleware' => 'admin.promise'], function() {
	Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
	Route::post('/login', 'Auth\LoginController@tryLogin')->name('login');
});

Route::get('jwt/get/token', 'JWTTokenController@getToken');

// Dev Page
Route::get('/admin-dev', 'DeveloperController@getIndex');
