<?php
    require_once('views/reports/reports_table_view_impl.php');
    require_once('views/reports/reports_thumbnails_view_impl.php');
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

            $sort_column        = 'date';
            $sort_ascending     = false;

            $view_as            = 'list';
            $filter             = '';

            if (ENABLE_FRIENDLY_URLS)
            {
                $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)

                $range = get_date_range_from_url($path);

                if (count($range) === 2)
                {
                    if (!empty($range[0]) && !empty($range[1]) )
                    {
                        $date_from_str  = $range[0];
                        $date_to_str    = $range[1];
                    }
                }
            }

            if (isset($_GET['view']) )
            {
                $view_as = $_GET['view'];
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
                $filter         = $_GET['filter'];
            }

            if (isset($_GET['from']) && isset($_GET['to']) )
            {
                $date_from_str  = date_str_to_iso($_GET['from']);
                $date_to_str    = date_str_to_iso($_GET['to']);
            }

            if (!empty($date_from_str) && !empty($date_to_str) )
            {
                $reports = Reports::get_all_in_range($date_from_str, $date_to_str, $filter, $sort_column, $sort_ascending);
            }
            else
            {
                // Store all the reports in a variable
                $reports = Reports::get_all($filter, $sort_column, $sort_ascending);
            }

            require_once('views/reports/index.php');
        }


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