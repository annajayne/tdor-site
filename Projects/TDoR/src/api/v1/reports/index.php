<?php
    /**
     * JSON class.
     *
     */


    require_once('./../../../defines.php');
    require_once('./../../../db_credentials.php');
    require_once('./../../../connection.php');
    require_once('./../../../display_utils.php');
    require_once('./../../../misc.php');
    require_once('./../../../models/report.php');
    require_once('./json_response.php');
    
   
   
   /**
    * Extract the parameters of the query.
    *
    * @return JsonParameters                  The parameters of the query.
    */
    function get_parameters()
    {
        $parameters = new JsonParameters();

        if (isset($_GET['from']) )
        {
            $parameters->date_from = $_GET['from'];
        }

        if (isset($_GET['to']) )
        {
            $parameters->date_to = $_GET['to'];
        }

        if (isset($_GET['country']) )
        {
            $parameters->country = $_GET['country'];
        }

        if (isset($_GET['filter']) )
        {
            $parameters->filter = $_GET['filter'];
        }

        if (isset($_GET['uid']) )
        {
            $parameters->uid  = $_GET['uid'];
        }

        if (empty($uid) && isset($_GET['url']) )
        {
            $parameters->url  = $_GET['url'];
        }
        return $parameters;
    }


    function get_json_status($parameters)
    {
        $status = new JsonStatus();

        $status->code = 200;

        return $status;
    }


    function get_reports_data($parameters)
    {
        $reports = array();
        
        $date_from  = $parameters->date_from;
        $date_to    = $parameters->date_to;

        if (!empty($date_from) || !empty($date_to) )
        {
            if (empty($date_from) || empty($date_to) )
            {
                // If the 'from' or 'to' date has been specified but the other is blank, fill it in from the database
                $dates      = Reports::get_date_range();

                $date_from  = empty($date_from) ? $dates[0] : $date_from;
                $date_to    = empty($date_to) ? $dates[1] : $date_to;
            }

            $reports        = Reports::get_all_in_range($date_from, $date_to, $parameters->country, $parameters->filter);
        }
        else
        {
            $reports        = Reports::get_all($parameters->country, $parameters->filter);
        }
        
        $data = new JsonReportsData();

        $data->reports_count = count($reports);

        foreach ($reports as $report)
        {
            $report_data = new JsonReportDataSummary();

            $report_data->set_from_report($report);

            $data->reports[] = $report_data;
        }
        return $data;
    }


    function get_report_data($uid)
    {
        $id = Reports::find_id_from_uid($uid);

        if ($id > 0)
        {
            $report = Reports::find($id);

            if ($report != null)
            {
                $data = new JsonReportData();

                $data->set_from_report($report);

                return $data;
            }
        }
        return null;
    }


    $request_method = $_SERVER["REQUEST_METHOD"];

    if ($request_method === 'GET')
    {
        $parameters = get_parameters();

        $uid = $parameters->uid;
        if (empty($parameters->uid) && !empty($parameters->url) )
        {
            $uid_len = 8;
            if (strlen($url) > $uid_len)
            {
                $uid = substr($parameters->url, -$uid_len);
            }
        }

        // Create the response object and assign its properties
        $response = new JsonReponse();

        $response->parameters = $parameters;

        $response->status = get_json_status($parameters);

        // If the response status is OK, fill in the data. Otherwise, return an error
        if ($response->status->code === 200)
        {
            if (!empty($uid) )
            {
                // Retrieve the data for a single specified report
                $response->data = get_report_data($uid);

                if ( ($response->data === null) || ($response->data->name === null) )
                {
                    // UID/URL not found
                    $response->status->code = 404;
                }
            }
            else
            {
                // Retrieve the data for a collection of reports, guided by the given parameters
                $response->data = get_reports_data($parameters);
            }
        }

        $status = HTTPStatus($response->status->code);

        $response->status->description = $status['error'];

        $json = $json = json_encode($response);

        if ($response->status->code != 200)
        {
            header($response->status->description, TRUE, $response->status->code);
        }

        header('Content-type: application/json');

        echo $json;
    }
    else
    {
        header("HTTP/1.0 405 Method Not Allowed");
    }

?>
