<?php
    /**
     * JSON class.
     *
     */

    set_include_path(get_include_path().PATH_SEPARATOR.'./../../../');

    require_once('defines.php');
    require_once('util/misc.php');
    require_once('util/utils.php');
    require_once('models/db_credentials.php');
    require_once('models/connection.php');
    require_once('display_utils.php');
    require_once('models/db_utils.php');
    require_once('models/reports.php');
    require_once('models/users.php');
    require_once('json_response.php');



   /**
    * Extract the parameters of the query.
    *
    * @return JsonParameters                  The parameters of the query.
    */
    function get_parameters()
    {
        $parameters = new JsonParameters();

        if (isset($_GET['key']) )
        {
            $parameters->api_key = $_GET['key'];
        }

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

        if (isset($_GET['category']) )
        {
            $parameters->category = $_GET['category'];
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


    function get_json_status($parameters, $status_code = 0)
    {
        $status = new JsonStatus();

        $status->code = ($status_code > 0) ? $status_code : 200;

        return $status;
    }


    function get_reports_data($parameters)
    {
        $reports                    = array();

        $db                         = new db_credentials();
        $reports_table              = new Reports($db);

        $query_params               = new ReportsQueryParams();

        $query_params->date_from    = $parameters->date_from;
        $query_params->date_to      = $parameters->date_to;
        $query_params->country      = $parameters->country;
        $query_params->category     = $parameters->category;
        $query_params->filter       = $parameters->filter;

        if (!empty($date_from) || !empty($date_to) )
        {
            if (empty($date_from) || empty($date_to) )
            {
                // If the 'from' or 'to' date has been specified but the other is blank, fill it in from the database
                $dates                      = $reports_table->get_date_range();

                $query_params->date_from    = empty($query_params->date_from) ? $dates[0] : $query_params->date_from;
                $query_params->date_to      = empty($query_params->date_to) ? $dates[1] : $query_params->date_to;
            }
        }

        $reports                    = $reports_table->get_all($query_params);

        $data                       = new JsonReportsData();

        $data->reports_count        = count($reports);

        foreach ($reports as $report)
        {
            $report_data            = new JsonReportDataSummary();

            $report_data->set_from_report($report);

            $data->reports[]        = $report_data;
        }
        return $data;
    }


    function get_report_data($uid)
    {
        $db                         = new db_credentials();
        $reports_table              = new Reports($db);

        $id                         = $reports_table->find_id_from_uid($uid);

        if ($id > 0)
        {
            $report                 = $reports_table->find($id);

            if ($report != null)
            {
                if (empty($report->tweet) )
                {
                    $summary_text   = get_summary_text($report);

                    $report->tweet  = $summary_text['desc'];
                }

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
        $status_code    = 0;

        $parameters     = get_parameters();

        $db             = new db_credentials();
        $users_table    = new Users($db);

        $uid            = $parameters->uid;
        if (empty($parameters->uid) && !empty($parameters->url) )
        {
            $uid_len = 8;
            if (strlen($parameters->url) > $uid_len)
            {
                $uid = substr($parameters->url, -$uid_len);
            }

            if (empty($uid) )
            {
                $status_code = 404;
            }
        }

        // Create the response object and assign its properties
        $response = new JsonReponse();

        $response->parameters = $parameters;

        $response->status = get_json_status($parameters, $status_code);

        if (empty($parameters->api_key) || !$users_table->get_user_from_api_key($parameters->api_key) )
        {
            // No API key provided - access denied.
            $response->status->code = 403;
        }

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

        $status = GetHttpStatus($response->status->code);

        header($status['error']);

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
