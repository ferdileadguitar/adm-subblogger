<?php

Auth::routes();

// Admin Pages
// ------------------------------------------------------------------------
Route::group(['middleware' => ['admin', 'web']], function() {

	// Contents
	Route::get('/', 'Pages\ContentController@index')->name('content');
	Route::get('/contents', 'Pages\ContentController@index')->name('contentWithURI');
	Route::get('users/{user_slug}', 'Pages\ContentController@getPost')->name('contentWithUser');

	// Author
	Route::get('authors', 'Pages\AuthorController@index')->name('authorWithURI');

	// Channel & Formats
	Route::get('channels', 'Pages\ChannelController@index')->name('channelWithURI');

	Route::get('settings', 'Pages\AdminSettings@getHome')->name('homeSettings');
	Route::post('access/user', 'Pages\AdminSettings@addAccessUser')->name('accessUser');
	Route::get('access/user/rm', 'Pages\AdminSettings@deletedAccessUser')->name('delAccessUser');
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
Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');