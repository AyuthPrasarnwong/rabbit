<?php

namespace rabbit\Http\Controllers;

use Illuminate\Http\Request;

use Twitter;
use File;
use TwitterOAuth;

class TwitterController extends Controller
{
    public function twitterUserTimeLine()
    {
    	$data = Twitter::getHomeTimeline(['count' => 10, 'format' => 'array']);
    	//$apiPlace = json_decode('https://api.twitter.com/1.1/search/tweets.json?q=%3A01039fff52b33fc8');
    	//dd($apiPlace);
    	$connection = new TwitterOAuth(
    		env('TWITTER_CONSUMER_KEY', ''), 
    		env('TWITTER_CONSUMER_SECRET', ''), 
    		env('TWITTER_ACCESS_TOKEN', ''), 
    		env('TWITTER_ACCESS_TOKEN_SECRET', '')
    	);
    	//$statuses = $connection->get("statuses/home_timeline", ["count" => 1, "exclude_replies" => true]);

    	//dd(urldecode ( 'place%3A07d9cd6afd884001' ) );
    	//https://api.twitter.com/1.1/search/tweets.json?q=&geocode=-22.912214,-43.230182,1km&lang=pt&result_type=recent
     	//$statuses = $connection->get("search/tweets", ["q" => urldecode ( '%40twitterapi' ), 
     												// 	"geocode" => '37.781157 -122.398720 50km',
    													// //"place" => urldecode ( '3A07d9cd6afd884001') , 
    													// "lang" => 'th', 
    													// //"result_type" => 'recent' 
    													// "count" => 10,
    													// ]);

     	$statuses = $connection->get("geo/search", ["query" => urldecode ( 'New York' ), 
     												// 	"geocode" => '37.781157 -122.398720 50km',
    													// //"place" => urldecode ( '3A07d9cd6afd884001') , 
    													// "lang" => 'th', 
    													// //"result_type" => 'recent' 
    													// "count" => 10,
    													]);
     	//https://api.twitter.com/1.1/geo/search.json?query=Toronto
		//$content = $connection->get("account/verify_credentials");
    	dd($statuses->result->places); 
    	return view('welcome',compact('data'));
    }

	public function searchTweet(Request $request)
	{
		//dd($request->all());
		// return redirect()->action('Erp\InquireController@index', ['companyID' => $companyID])
		// 	->with('message', trans('คุณได้สร้าง Inquire Sheet เรียบร้อยแล้ว'));

		$connection = new TwitterOAuth(
    		env('TWITTER_CONSUMER_KEY', ''), 
    		env('TWITTER_CONSUMER_SECRET', ''), 
    		env('TWITTER_ACCESS_TOKEN', ''), 
    		env('TWITTER_ACCESS_TOKEN_SECRET', '')
    	);

    	$statuses = $connection->get("geo/search", ["query" => urldecode ( $request['city_name'] ), 
     												// 	"geocode" => '37.781157 -122.398720 50km',
    													// //"place" => urldecode ( '3A07d9cd6afd884001') , 
    													// "lang" => 'th', 
    													// //"result_type" => 'recent' 
    													//"count" => 100,
    													]);

    	//dd( $statuses->result->places );
    	
    	if ( empty( $statuses->result->places ) ) {

    		return redirect()->action('TwitterController@index')
						->with('error', trans('Not Found Data!'));
    	}

    	$places = $statuses->result->places;

    	//dd($places);

    	//$collectPlace = collect([]);

    	$city = [];

    	//dd($collection);
    	foreach ($places as $key => $place) {
    		if( $place->name == $request['city_name']) {
    			$city[] = $place;
    		}

    	}

    	if ( empty( $city ) ) {

    		return redirect()->action('TwitterController@index')
						->with('error', trans('Not Found Data!'));
    	}

    	//dd($city);

    	$cityId = $city[0]->id;

    	$centroid = $city[0]->centroid;

    	$fullName = $city[0]->full_name;

    	$geocode = "$centroid[1],$centroid[0],50km";

    	//dd($geocode);

    	//https://api.twitter.com/1.1/search/tweets.json?q=&geocode=-22.912214,-43.230182,1km&lang=pt&result_type=recent
    	$statuses = $connection->get("search/tweets", [ "q" => 'now', 
	     												//"geocode" => $geocode,
	    												"place" => $cityId , 
	    												//"lang" => 'th', 
	    												"result_type" => 'recent', 
	    												//"count" => 100,
    												]);
    	//$centroid = $collection->toJson();

    	//dd($statuses);
    	$collect = collect();
    	  	//dd($collect);
    	foreach ($statuses->statuses as $key => $status) {
    		//dd($status);
    	
    		// $collect->push( array( array( 'text' => $status->text,
    		// 								'user_id' => $status->user->id,
    		// 								'profile_image_url' => $status->user->profile_image_url,
    		// 								'name' => $status->user->name,
    		// 								'screen_name' =>  $status->user->screen_name,
    		// 								'location' => $status->user->location,				
    		// ) ) );	
    		$collect->push($status->user->profile_image_url);									
    		
    	}

  		//dd($collect->toArray());
    
    
    
		return redirect()->action('TwitterController@index')
						->with('message', trans('Search Completed!'))
						->with('long', $centroid[0])
						->with('lat', $centroid[1])
						->with('tweets', $collect)
						->with('fullName', $fullName);
  		
		//return redirect()->with(['lat' => $centroid[0], 'long' => $centroid[1] ])
			//->with('message', trans('Search Completed!'));
				
	}

    public function tweet(Request $request)
    {
    	$this->validate($request, [
        		'tweet' => 'required'
        	]);

    	$newTwitter = ['status' => $request->tweet];

    	if(!empty($request->images)){
    		foreach ($request->images as $key => $value) {
    			$uploaded_media = Twitter::uploadMedia(['media' => File::get($value->getRealPath())]);
    			if(!empty($uploaded_media)){
                    $newTwitte['media_ids'][$uploaded_media->media_id_string] = $uploaded_media->media_id_string;
                }
    		}
    	}

    	$twitter = Twitter::postTweet($newTwitter);

    	return back();
    }

    //http://twitter.com/search?q=place%3A07d9cd6afd884001


	public function index() {
	    $lat = '-25.363';
    	$long = '100';
		return view('welcome', compact('lat', 'long'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//
	}
}
