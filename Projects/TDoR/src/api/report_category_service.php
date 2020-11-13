<?php
    /**
     * Support service to allow the add/edit report pages to query the default category of a report given the cause.
     *
     */

    require_once('./../models/reports.php');


    class Response
    {
        public $result          = false;

        public $cause           = '';
        public $category        = '';
    }


    $response                   = new Response();

    $response->result           = false;

    if (isset($_POST['cause']) )
    {
        $response->cause         = $_POST["cause"];
    }

    $report = new Report();

    $report->cause              = $response->cause;

    $response->category         = Report::get_category($report);
    $response->result           = true;


    header('Content-type: application/json');

    echo json_encode($response);

?>
