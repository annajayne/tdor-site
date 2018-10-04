<?php
    /**
     *  Controller for reports (database pages).
     *
     */


    require_once('views/reports/reports_table_view_impl.php');
    require_once('views/reports/reports_thumbnails_view_impl.php');
    require_once('views/reports/reports_details_view_impl.php');


    /**
     * Parameter class for actions
     */
    class reports_params
    {
        /** @var boolean                    Whether reports are available. */
        public  $reports_available;

        /** @var array                      The date range of all available reports. */
        public  $report_date_range;

        /** @var string                     The start date. */
        public  $date_from_str;

        /** @var string                     The end date. */
        public  $date_to_str;

        /** @var string                     The view (list, thumbnails or details). */
        public  $view_as;

        /** @var string                     The filter to apply. */
        public  $filter;

        /** @var int                        The id of the report to display. */
        public  $id;
}


    /**
     *  Controller for reports (database pages)
     *
     *  Supported actions:
     *
     *      'index'  - Show the "Reports" page.
     *      'show'   - Show an individual "Report" page.
     *      'add'    - Add a new report.
     *      'edit'   - Edit an existing report.
     *      'delete' - Delete an existing report.
     */
    class ReportsController
    {
        /**
         *  Get the id of the report to display from the current URL.
         *
         *  The id may be encoded as either an id (integer) or uid (hex string).
         *
         *  @return int                   The id of the report to display.
         */
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


        /**
         *  Get the parameters of the report to display.
         *
         *  @param boolean $setcookies      true if cookies should be set; false otherwise.
         *  @return reports_params          The parameters of the report to display.
         */
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


        /**
         *  Show the reports page.
         */
        public function index()
        {
            $params = self::get_current_params(true);

            require_once('views/reports/index.php');
        }


        /**
         *  Show the current report.
         */
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

            // Check that the invoked URL is the correct one - if not redirect to it.
            // BODGE ALERT: headers have already been sent by this point, so we use a Javascript redirect here instead.
            $current_link   = $_SERVER[REQUEST_URI];
            $permalink      = get_permalink($report);

            if ($current_link != $permalink)
            {
                $url = raw_get_host().$permalink;

                echo "<script>window.location.replace('$url');</script>";
            }

            require_once('views/reports/show.php');
        }


        /**
         *  Add a report.
         */
        public function add()
        {
            require_once('models/report.php');
            require_once('views/reports/add.php');
        }


        /**
         *  Edit the current report.
         */
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


        /**
         *  Delete the current report.
         */
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