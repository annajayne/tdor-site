<?php
    /**
     * Support service to allow the add/edit report pages to query the default tweet text given the name, date etc.
     * 
     */

    require_once('./../models/report.php');
    require_once('./../display_utils.php');


    class Response
    {
        public $result          = false;

        public $name            = '';
        public $age             = '';
        public $date            = '';
        public $location        = '';
        public $country         = '';
        public $cause           = '';

        public $tweet           = '';
    }


    $response                   = new Response();

    $response->result           = false;

    if (isset($_POST['name']) )
    {
        $response->name         = $_POST["name"];
    }
    if (isset($_POST['age']) )
    {
        $response->age          = $_POST["age"];
    }
    if (isset($_POST['date']) )
    {
        $response->date         = $_POST["date"];
    }
    if (isset($_POST['location']) )
    {
        $response->location	    = $_POST["location"];
    }
    if (isset($_POST['country']) )
    {
        $response->country      = $_POST["country"];
    }
    if (isset($_POST['cause']) )
    {
        $response->cause        = $_POST["cause"];
    }
    $report = new Report();
        
    $report->name               = $response->name;
    $report->age                = $response->age;
    $report->date               = $response->date;
    $report->location           = $response->location;
    $report->country            = $response->country;
    $report->cause              = $response->cause;

    $response->tweet            = get_tweet_text($report);

    $response->result           = true;


    header('Content-type: application/json');

    echo json_encode($response);

?>
