<?php

Auth::routes();

// Admin Pages
// ------------------------------------------------------------------------
Route::group(['middleware' => 'admin'], function() {
	// Contents
	Route::get('/', 'Pages\ContentController@index')->name('content');
	Route::get('/contents', 'Pages\ContentController@index')->name('contentWithURI');

	Route::get('users/{username}', 'Pages\ContentController@getPost')->name('contentWithUser');
});

// Login - Logout
// ------------------------------------------------------------------------
Route::group(['middleware' => 'admin.promise'], function() {
	Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
	Route::post('/login', 'Auth\LoginController@tryLogin')->name('login');
});

Route::get('jwt/get/token', 'JWTTokenController@getToken');
