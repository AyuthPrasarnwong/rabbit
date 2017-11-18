<!DOCTYPE html>
<html lang="en">
     <head>
        <title>Map Based Search Application</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert.css') }}">
        <style>
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
            }
        
            #floating-panel {
                position: absolute;
                z-index: 10000;
                text-align: center;
                width: 50%;
                top: 5%;
                left: 25%;
                padding: 5px;
                background-color: #fff;
                border: 1px solid #999;
            }
            #city-text {
                margin: auto;
                color: red; 
                text-transform: uppercase;
                text-shadow: 3px 2px blue;
            }
        </style>
    </head>

    <body>

        <div id="floating-panel"><h2 id="city-text"></h2></div>
        <div id="map" style="position:fix; width: 100%; height: 95%;"></div>
        <div id="form" class="form-group" style="width: 100%; height: 5%;">
            <div class="input-group">
                <input id="address" type="text" class="form-control" placeholder="City Name">
                <!-- <input id="fullname" type="hidden" value=""> -->
                    <span class="input-group-btn">
                        <button id="submit" class="btn btn-primary" value="Geocode">
                            <span class="glyphicon glyphicon-search" aria-hidden="true"></span>Search
                        </button>
                    </span>
            </div>
        </div> 
        
       
    </body>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js"></script> -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <!-- script async defer -->
    <script 
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBr9LJNSJr8-i35_sStZslzRgi1nherrXU">
    </script>
    <script>
        var geocoder;
        var map;
        var markers = [];
        initMap();
        
        $( document ).ready(function() {
            // Handler for .ready() called.
            $( '#floating-panel' ).hide();
            $( '#address' ).keydown(function(event) {

                if ( event.which == 13 ) {
                    $( '#submit' ).click();
                    
                }
            });
        });
        // $( '#form' ).validate({
        //     rules: {
        //         city_name: {
        //             required: true,
        //             // Using the normalizer to trim the value of the element
        //             // before validating it.
        //             //
        //             // The value of `this` inside the `normalizer` is the corresponding
        //             // DOMElement. In this example, `this` references the `username` element.
        //             normalizer: function(value) {
        //                 return $.trim(value);
        //             }
        //         }
        //     }
        // });
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 3,
                center: {
                    lat: 0, 
                    lng: 0
                }
            });
            geocoder = new google.maps.Geocoder();
            search(map, geocoder);
        }

        function search(map, geocoder) {
            document.getElementById('submit').addEventListener('click', function() {
                var address = $( '#address' ).val();
                geocoder.geocode( { 'address': address}, function(results, status) {
                    if (status == 'OK') {
                        $( '#address' ).val('');
                 
                        var text = 'Tweets about ' + results[0].formatted_address;
                       
                        //map.setCenter(results[0].geometry.location);
                        var param = { lat: results[0].geometry.location.lat(), lng: results[0].geometry.location.lng() };

                        $.getJSON( "/api/tweets", param, function( dataTweets ) {   
                            
                            var bound = new google.maps.LatLngBounds();
                            deleteMarkers();
                            console.log('dataTweets', dataTweets);

                            dataTweets.forEach(function( tweet, index ) { 
                                if(tweet.coordinates !== null) {
                                    //alert('Display only the tweets that contain coordinate data');
                                    var marker = createMarker(map, tweet, index);

                                    var contentString = '<div id="content">' +
                                        '<div id="siteNotice">' +
                                        '</div>' +
                                        '<div id="bodyContent">' +
                                        '<p><b>Tweet: </b>' + tweet.text + '</p>' +
                                        '<p><b>When: </b>' + tweet.created_at +'.</p>' +
                                        '</div>' + 
                                        '</div>';

                                    var infowindow = new google.maps.InfoWindow({
                                        content: contentString
                                    });

                                    marker.addListener('click', function() {
                                        infowindow.open(map, marker);
                                    });
                                    bound.extend(marker.getPosition());
                                    markers.push(marker);
                                    // infowindow.open(map, marker);
                                    
                                } else {
                                    // tweet.coordinates === null
                                }
                            });

                            if(markers.length > 0) {
                                
                                map.setZoom(8);
                                map.setCenter(bound.getCenter());
                                //console.log("bound ", bound)
                                //console.log("bound center", bound.getCenter())

                                swal({
                                  title: "Success!",
                                  text: 'There are ' + markers.length + ' tweets that contain coordinate data',
                                  type: "success",
                                  confirmButtonText: "OK"
                                });

                                showCityText(text);


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

        function createMarker(map, tweet, index) {
            return new google.maps.Marker({
                position: {
                    lat: tweet.coordinates.coordinates[1], 
                    lng: tweet.coordinates.coordinates[0]
                },
                icon: tweet.user.profile_image_url,
                map: map,
                animation: google.maps.Animation.DROP,
                title: tweet.place.full_name,
                zIndex: index
            });
        }

        function showCityText(text) {
            $( '#floating-panel' ).show();
            $( '#city-text' ).text(text);
            // var sidediv = document.getElementById('text-show');
            // sidediv.innerHTML = text;
        }

        function setMapOnAll(map) {
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(map);
            }
        }
        // Removes the markers from the map, but keeps them in the array.
        function clearMarkers() {
            setMapOnAll(null);
        }
        // Shows any markers currently in the array.
        function showMarkers() {
            setMapOnAll(map);
        }

        // Deletes all markers in the array by removing references to them.
        function deleteMarkers() {
            clearMarkers();
            markers = [];
        }

    

    </script>
</html>
