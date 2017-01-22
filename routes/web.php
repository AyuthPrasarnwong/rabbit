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

Route::get('/', function () {
    //$centroid = json_encode([-25.363 , 125.552]);
    // $lat = '-25.363';
    // $long = '100';
	return view('welcome');
});

// Route::get('twitterUserTimeLine', 'TwitterController@twitterUserTimeLine');
// Route::post('searchTweet', ['as'=>'search.tweet','uses'=>'TwitterController@searchTweet']);
// Route::resource('tweet', 'TwitterController');