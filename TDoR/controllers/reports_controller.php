<?php
    require_once('views/reports/reports_table_view_impl.php');
    require_once('views/reports/reports_details_view_impl.php');


    // Controller for reports (database pages)
    //
    // Supported actions:
    //
    //      index
    //      show
    //      create (TODO)
    //      update (TODO)
    //
    class ReportsController
    {
        public function index()
        {
            $reports_available  = Reports::has_reports();
            $report_date_range  = Reports::get_date_range();

            $tdor_to_year       = get_tdor_year(new DateTime($report_date_range[1]) );

            $date_from_str      = ($tdor_to_year - 1).'-10-01';
            $date_to_str        = $tdor_to_year.'-09-30';

            $filter             = '';

            if (isset($_GET['filter']) )
            {
                $filter         = $_GET['filter'];
            }

            if (isset($_GET['from']) && isset($_GET['to']) )
            {
                $date_from_str  = date_str_to_iso($_GET['from']);
                $date_to_str    = date_str_to_iso($_GET['to']);
            }

            if (!empty($date_from_str) && !empty($date_to_str) )
            {
                $reports = Reports::get_all_in_range($date_from_str, $date_to_str, $filter);
            }
            else
            {
                // Store all the reports in a variable
                $reports = Reports::get_all($filter);
            }

            require_once('views/reports/index.php');
        }


        public function show()
        {
            $id = 0;

            if (ENABLE_FRIENDLY_URLS)
            {
                $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
                $elements = explode('/', $path);                // Split path on slashes

                // e.g. tdor.annasplace.me.uk/reports/year/month/day/name
                $element_count = count($elements);

                if ( ($element_count == 5) && ($elements[0] == 'reports') )
                {
                    $year       = $elements[1];
                    $month      = $elements[2];
                    $day        = $elements[3];

                    $name       = urldecode($elements[4]);

                    $name_len   = strlen($name);

                    $uid_len = 8;
                    $uid_delimiter_pos = $name_len - ($uid_len + 1);

                    if ( ($name_len > $uid_len) && ($name[$uid_delimiter_pos] === '-') )
                    {
                        $uid = substr($name, -$uid_len);

                        // Validate
                        if (is_valid_hex_string($uid) )
                        {
                            $id = Reports::find_id_from_uid($uid);
                        }
                    }
                }
            }

            if ( ($id == 0) && isset($_GET['uid']) )
            {
                $uid = $_GET['uid'];

                $id = Reports::find_id_from_uid($uid);
            }

            if ( ($id == 0) && isset($_GET['id']) )
            {
                $id = $_GET['id'];
            }

            // Our raw urls are of the form ?category=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $report = Reports::find($id);

            require_once('views/reports/show.php');
        }
    }


?>