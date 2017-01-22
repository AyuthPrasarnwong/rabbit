<?php

use Illuminate\Http\Request;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::get('places', function (Request $request) {
	//dd($request->all());

    $connection = new TwitterOAuth(
		env('TWITTER_CONSUMER_KEY', ''), 
		env('TWITTER_CONSUMER_SECRET', ''), 
		env('TWITTER_ACCESS_TOKEN', ''), 
		env('TWITTER_ACCESS_TOKEN_SECRET', '')
	);

    //$statuses = $connection->get("statuses/home_timeline", ["count" => 1, "exclude_replies" => true]);
   $statuses = $connection->get("geo/search", ["query" => $request['address'], 
     												// 	"geocode" => '37.781157 -122.398720 50km',
    													// //"place" => urldecode ( '3A07d9cd6afd884001') , 
    													// "lang" => 'th', 
    													// //"result_type" => 'recent' 
    													//"count" => 100,
    													]);
    return $statuses->result->places;
	//dd( $statuses->result->places );
	
	if ( empty( $statuses->result->places ) ) {

		return [];
	}



});


Route::get('/tweets', function (Request $request) {
	//dd($request->all());
    //return $request->user();
    $connection = new TwitterOAuth(
		env('TWITTER_CONSUMER_KEY', ''), 
		env('TWITTER_CONSUMER_SECRET', ''), 
		env('TWITTER_ACCESS_TOKEN', ''), 
		env('TWITTER_ACCESS_TOKEN_SECRET', '')
	);

	$lat = $request['lat'];
	$lng = $request['lng'];
	$geocode = "$lat,$lng,50km";

	$statuses = $connection->get("search/tweets", [ "q" => 'now', 
     												"geocode" => $geocode,
    												//"place" => $cityId , 
    												//"lang" => 'th', 
    												"result_type" => 'recent', 
    												"count" => 100,
												]);
	//dd($statuses->statuses);
	return $statuses->statuses;

});