<?php

    require_once('./../geocode.php');


    class Response
    {
        public $result    = false;
        public $location			= '';
        public $country			= '';
        public $latitude  = 0;
        public $longitude = 0;
    }

    $response = new Response();

    $response->result = false;

    if (isset($_POST['location']) )
    {
        $response->location			= $_POST["location"];
    }

    // At least the country needs to be defined
    if (isset($_POST['country']) )
    {
        $response->country			= $_POST["country"];

        $place = array();

        $place['location']  = $response->location;
        $place['country']   = $response->country;

        $places = array();
        $places[] = $place;

        $geocoded_places    = geocode(array($place) );

        if (!empty($geocoded_places) )
        {
            $geocoded = $geocoded_places[0];

            $response->latitude   = $geocoded['lat'];
            $response->longitude  = $geocoded['lon'];

            $response->result = true;   
        }
    }


    header('Content-type: application/json');

    echo json_encode($response);

?>
