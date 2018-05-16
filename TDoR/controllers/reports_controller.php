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
            $reports_available  = Report::has_reports();
            $report_date_range  = Report::get_minmax_dates();

            $tdor_to_year       = get_tdor_year(new DateTime($report_date_range[1]) );

            $date_from_str      = '1 Oct '.($tdor_to_year - 1);
            $date_to_str        = '30 Sep '.$tdor_to_year;

            if (isset($_GET['filter']) )
            {
                $filter         = $_GET['filter'];
            }

            if (isset($_GET['from']) && isset($_GET['to']) )
            {
                $date_from_str  = $_GET['from'];
                $date_to_str    = $_GET['to'];
            }


            if (!empty($date_from_str) && !empty($date_to_str) )
            {
                $reports = Report::all_in_range($date_from_str, $date_to_str, $filter);
            }
            else
            {
                // Store all the reports in a variable
                $reports = Report::all($filter);
            }

            require_once('views/reports/index.php');
        }


        public function show()
        {
            // We expect a url of the form ?controller=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if (!isset($_GET['id']) )
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $item = Report::find($_GET['id']);

            require_once('views/reports/show.php');
        }
    }


?>