<?php
    require_once('views/reports/reports_table_view_impl.php');
    require_once('views/reports/reports_thumbnails_view_impl.php');
    require_once('views/reports/reports_details_view_impl.php');


    // Parameter class for actions
    //
    class reports_params
    {
        public  $reports_available;
        public  $report_date_range;

        public  $date_from_str;
        public  $date_to_str;
        public  $view_as;
        public  $filter;

        public  $id;
}


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
        private function get_current_id()
        {
            $id = 0;

            if (ENABLE_FRIENDLY_URLS)
            {
                $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
                $uid = get_uid_from_friendly_url($path);

                // Validate
                if (is_valid_hex_string($uid) )
                {
                    $id = Reports::find_id_from_uid($uid);
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
            return $id;
        }


        public function get_current_params($setcookies = false)
        {
            $params                     = new reports_params();

            $params->id                 = self::get_current_id();

            $params->reports_available  = Reports::has_reports();
            $params->report_date_range  = Reports::get_date_range();

            $tdor_to_year               = get_tdor_year(new DateTime($params->report_date_range[1]) );

            $params->date_from_str      = ($tdor_to_year - 1).'-10-01';
            $params->date_to_str        = $tdor_to_year.'-09-30';

            $sort_column                = 'date';
            $sort_ascending             = false;

            $params->date_from_str      = get_cookie(DATE_FROM_COOKIE,  $params->date_from_str);
            $params->date_to_str        = get_cookie(DATE_TO_COOKIE,    $params->date_to_str);
            $params->view_as            = get_cookie(VIEW_AS_COOKIE,    'list');
            $params->filter             = get_cookie(FILTER_COOKIE,     '');

            if (ENABLE_FRIENDLY_URLS)
            {
                $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)

                $range = get_date_range_from_url($path);

                if (count($range) === 2)
                {
                    if (!empty($range[0]) && !empty($range[1]) )
                    {
                        $params->date_from_str  = $range[0];
                        $params->date_to_str    = $range[1];

                        if ($setcookies)
                        {
                            // If $setcookies is true (it should be only for the "reports" page), store the data params.
                            // NB this is a bit of a bodge - it would be good to move this logic elseware.
                            set_cookie(DATE_FROM_COOKIE, $params->date_from_str);
                            set_cookie(DATE_TO_COOKIE,   $params->date_to_str);
                        }
                    }
                }
            }

            if (isset($_GET['view']) )
            {
                $params->view_as = $_GET['view'];
            }

            if (isset($_GET['sortby']) )
            {
                $sort_column    = $_GET['sortby'];
            }

            if (isset($_GET['sortup']) )
            {
                $sort_ascending = ( ( (int)$_GET['sortup']) > 0) ? true : false;
            }

            if (isset($_GET['filter']) )
            {
                $params->filter         = $_GET['filter'];
            }

            if (isset($_GET['from']) && isset($_GET['to']) )
            {
                $params->date_from_str  = date_str_to_iso($_GET['from']);
                $params->date_to_str    = date_str_to_iso($_GET['to']);
            }

            if ($params->id > 0)
            {
                $report = Reports::find($params->id);

                $params->reports = array($report);
            }
            else if (!empty($params->date_from_str) && !empty($params->date_to_str) )
            {
                $params->reports = Reports::get_all_in_range($params->date_from_str, $params->date_to_str, $params->filter, $sort_column, $sort_ascending);
            }
            else
            {
                // Store all the reports in a variable
                $params->reports = Reports::get_all($params->filter, $sort_column, $sort_ascending);
            }
            return $params;
        }


        public function index()
        {
            $params = self::get_current_params(true);

            require_once('views/reports/index.php');
        }


        public function show()
        {
            $id = self::get_current_id();

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


        public function add()
        {
            require_once('models/report.php');
            require_once('views/reports/add.php');
        }


        public function edit()
        {
            $id = self::get_current_id();

            // Our raw urls are of the form ?category=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $report = Reports::find($id);

            require_once('views/reports/edit.php');
        }


        public function delete()
        {
            $id = self::get_current_id();

            // Our raw urls are of the form ?category=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $report = Reports::find($id);

            require_once('views/reports/delete.php');
        }


    }


?>