<!DOCTYPE html>
<html lang="en">
     <head>
        <title>Map Based Search Application</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert.css') }}">
        <style>

          
            #map {
                height: 100%;
            }
              /* Optional: Makes the sample page fill the window. */
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
            }
           #content-window {
           
            position: absolute;
 
            z-index: 10000;
      
            text-align: center;
            width: 100%;
          

            
                /*     border-style: solid;
            border-width: 2px 2px 2px 2px;
            border-color: rgba(81, 203, 238, 1);
            border-radius: 5px;*/

          }
          #text-show {
            color: red; 
            text-transform: uppercase;
            text-shadow: 3px 2px blue;
          }

   
        </style>
    </head>

    <body>
        <div id="content-window"><h2 id="text-show"></h2></div>
            <div class="form-group" style=" position: absolute; bottom: 0;  margin: auto; width: 100%;">
                <div class="input-group">
                    <input id="address" type="text" name="city_name" class="form-control" placeholder="City Name" required>
                    <input id="fullname" type="hidden" value="">
                        <span class="input-group-btn">
                            <button id="submit" class="btn btn-primary" value="Geocode"><span class="glyphicon glyphicon-search" aria-hidden="true"></span> Search</button>
                          
                        </span>
                </div>
            </div>
        <div id="map"></div>
    </body>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script>

        var lat = 0;
        var long = 0;
        var geocoder;
        var map;



        // console.log('lat = ' + lat);
        // console.log('long = ' + long);

        function initMap() {

            var uluru = {
                lat: lat, 
                lng: long
            };

            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 2,
                center: uluru
            });

            geocoder = new google.maps.Geocoder();


            function showInContentWindow(text) {
              var sidediv = document.getElementById('text-show');
              sidediv.innerHTML = text;
            }

             
            document.getElementById('submit').addEventListener('click', function() {

                var address = document.getElementById('address').value;
                geocoder.geocode( { 'address': address}, function(results, status) {
                    if (status == 'OK') {

                        $( '#address' ).val('');
                        var text = 'Tweets about ' + results[0].formatted_address;
                        showInContentWindow(text);
                        map.setCenter(results[0].geometry.location);
                        //console.log(results[0]);
                        $.getJSON( "/api/tweets", { lat: results[0].geometry.location.lat(), lng: results[0].geometry.location.lng() }, function( dataTweets ) {   
                            
                            var i = 0; 
                            dataTweets.forEach(function( tweet, index ) { 

                                if(tweet.coordinates !== null) {
                                       //console.log(tweet.geo.coordinates[1]);
                                    console.log(tweet);

                                    //alert('Display only the tweets that contain coordinate data');

                                    var marker = new google.maps.Marker({
                                        position: {
                                            lat: tweet.coordinates.coordinates[1], 
                                            lng: tweet.coordinates.coordinates[0]
                                        },
                                        icon: tweet.user.profile_image_url,
                                        map: map,
                                        //title: place.full_name
                                        title: tweet.place.full_name

                                    });

                                    var contentString = '<div id="content">'+
                                      '<div id="siteNotice">'+
                                      '</div>'+
                                      '<div id="bodyContent">'+
                                      '<p><b>Tweet: </b>' + tweet.text + '</p>'+
                                      '<p><b>When: </b>' + tweet.created_at +'.</p>'+
                                      '</div>'+
                                      '</div>';

                                    var infowindow = new google.maps.InfoWindow({
                                        content: contentString
                                    });

                                    marker.addListener('click', function() {
                                        infowindow.open(map, marker);
                                    });

                                    i++;
                                } else {

                                }
                            });

                            if(i > 0) {
                                swal({
                                  title: "Success!",
                                  text: 'There are ' + i + ' tweets that contain coordinate data',
                                  type: "success",
                                  confirmButtonText: "Cool"
                                });
                                //alert('There are ' + i + ' tweets that contain coordinate data');
                            } else {
                                swal({
                                  title: "Error!",
                                  text: 'Each tweet do not contain coordinate data',
                                  type: "error",
                                  confirmButtonText: "Close"
                                });
                                //alert('All tweets do not contain coordinate data');
                            }  

                        }); 

                    } else {
                        swal({
                          title: "Error!",
                          text: 'Geocode was not successful for the following reason: ' + status,
                          type: "error",
                          confirmButtonText: "Close"
                        });
                        //alert('Geocode was not successful for the following reason: ' + status);
                    }
                });

            });
            
        }
    </script>
 
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBr9LJNSJr8-i35_sStZslzRgi1nherrXU&callback=initMap">
    </script>
</html>
