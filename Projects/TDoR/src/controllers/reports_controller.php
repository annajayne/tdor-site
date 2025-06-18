<?php
    /**
     *  Controller for reports (database pages).
     *
     */

    require_once('util/string_utils.php');                      // For is_valid_hex_string()
    require_once('util/datetime_utils.php');                    // For date_str_to_iso()
    require_once('util/account_utils.php');
    require_once('models/reports.php');
    require_once('models/report_utils.php');
    require_once('models/report_events.php');
    require_once('views/reports/reports_table_view_impl.php');
    require_once('views/reports/reports_thumbnails_view_impl.php');
    require_once('views/reports/reports_map_view_impl.php');
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

        /** @var string                     The country (or 'All'). */
        public  $country;

        /** @var string                     The category (or 'All'). */
        public  $category;

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
     *      'index'     -       Show the "Reports" page.
     *      'drafts'    -       Show the "Drafts" page.
     *      'show'      -       Show an individual "Report" page.
     *      'add'       -       Add a new report.
     *      'edit'      -       Edit an existing report.
     *      'publish'   -       Publish a draft report.
     *      'unpublish' -       Unpublish a published report.
     *      'update_thumbnail'  Update a report thumbnail
     *      'delete'    -       Delete an existing report.
     *      'undelete'  -       Undelete an existing report.
     */
    class ReportsController extends Controller
    {
        /**
         * Return the name of the controller
         *
         * @return string                                   The name of the controller.
         */
        public function get_name()
        {
            return 'reports';
        }


        /**
         * Return the names of the supported actions
         *
         * @return array                                    An array of the names of the actions supported by this controller.
         */
        public function get_actions()
        {
            return array('index',
                         'drafts',
                         'recent',
                         'show',
                         'add',
                         'edit',
                         'publish',
                         'unpublish',
                         'update_thumbnail',
                         'delete',
                         'undelete');
        }


        /**
         * Get the appropriate title for the given specified action on the given controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page title.
         */
        function get_page_title($action)
        {
            $title = 'Remembering Our Dead';

            $titles = array('index' =>           'Reports',
                            'show' =>            '');

            if (!empty($titles[$action]) )
            {
                $title = $title.' - '.$titles[$action];
            }
            return $title;
        }


        /**
         * Get the appropriate description for the given specified action on the given controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page description.
         */
        function get_page_description($action)
        {
            return $action;
        }


        /**
         * Get the appropriate keywords for the given specified action on the given controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page keywords.
         */
        function get_page_keywords($action)
        {
            return '';
        }


        /**
         * Get the appropriate thumbnail for the specified action on the controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page keywords.
         */
        function get_page_thumbnail($action)
        {
            $thumbnail = '/images/tdor_candle_jars.jpg';

            switch ($action)
            {
                case 'show';
                    $report = $this->get_current_report();

                    if ($report)
                    {
                        $thumbnail = get_thumbnail_pathname($report->photo_filename);
                    }
                    break;

                default:
                    break;
            }
            return append_path(raw_get_host(), $thumbnail);
        }


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

            $db                 = new db_credentials();
            $reports_table      = new Reports($db);

            if (ENABLE_FRIENDLY_URLS)
            {
                $path           = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
                $uid            = get_uid_from_friendly_url($path);

                // Validate
                if (is_valid_hex_string($uid) )
                {
                    $id = $reports_table->find_id_from_uid($uid);

                    // Special case - has this UID been replaced with another?
                    if ($id === 0)
                    {
                        switch ($uid)
                        {
                            // https://tdor.translivesmatter.info/reports/2019/03/15/name-unknown_ciudad-de-mexico-mexico_12c870d0 (original entry)
                            // https://tdor.translivesmatter.info/reports/2019/03/16/name-unknown_alcaldia-gustavo-a-madero-ciudad-de-mexico-mexico_47fc2b13 (duplicate entry)
                            case '47fc2b13':
                                $id = $reports_table->find_id_from_uid('12c870d0');
                                break;

                            // https://tdor.translivesmatter.info/reports/2019/03/11/name-unknown_jiutepec-morelos-mexico_43e750d0 (original entry)
                            // https://tdor.translivesmatter.info/reports/2019/03/10/name-unknown_jiutepec-morelos-mexico_f4f5b2b9 (duplicate entry)
                            case 'f4f5b2b9':
                                $id = $reports_table->find_id_from_uid('43e750d0');
                                break;

                            // https://tdor.translivesmatter.info/reports/2012/03/24/tyrell-jackson_riviera-beach-florida-usa_3a6d632f (original entry)
                            // https://tdor.translivesmatter.info/reports/2012/04/04/tyrell-jackson_riviera-beach-florida-usa_9e025f06 (duplicate entry)
                            case '9e025f06':
                                $id = $reports_table->find_id_from_uid('3a6d632f');
                                break;

                            // https://tdor.translivesmatter.info/reports/2011/12/29/githe-orlando-goines_new-orleans-louisiana-usa_25575810 (original entry)
                            // https://tdor.translivesmatter.info/reports/2011/12/30/githe-goines-_new-orleans-louisiana--usa_03fd6c02 (duplicate entry)
                            case '03fd6c02':
                                $id = $reports_table->find_id_from_uid('25575810');
                                break;

                            default:
                                break;
                        }
                    }
                }
            }

            if ( ($id === 0) && isset($_GET['uid']) )
            {
                $uid = $_GET['uid'];

                $id = $reports_table->find_id_from_uid($uid);
            }

            if ( ($id === 0) && isset($_GET['id']) )
            {
                $id = $_GET['id'];
            }
            return $id;
        }

        /**
         *  Get the current report.
         *
         *  @return Blogpost              The blogpost to display.
         */
        public function get_current_report()
        {
            $id = $this->get_current_id();

            if ($id > 0)
            {
                $db             = new db_credentials();
                $ereports_table = new Reports($db);

                $report         = $ereports_table->find($id);

                return $report;
            }
            return null;
        }

        /**
         *  Get the parameters of the report to display.
         *
         *  @param boolean $setcookies      true if cookies should be set; false otherwise.
         *  @return reports_params          The parameters of the report to display.
         */
        public function get_current_params($setcookies = false)
        {
            $db                         = new db_credentials();
            $reports_table              = new Reports($db);

            $params                     = new reports_params();

            $params->id                 = self::get_current_id();

            $params->reports_available  = $reports_table->has_reports();
            $params->report_date_range  = $reports_table->get_date_range();

            $tdor_year                  = date('Y');

            $latest_report_year         = date('Y', strtotime($params->report_date_range[1]) );

            if ($latest_report_year < $tdor_year)
            {
                // If there are no reports in the current year, use the most recent date for which we have data.
                $tdor_year = $latest_report_year;
            }

            $params->date_from_str      = ($tdor_year - 1).'-10-01';
            $params->date_to_str        = $tdor_year.'-09-30';

            $sort_column                = 'date';
            $sort_ascending             = false;

            $params->date_from_str      = get_cookie(DATE_FROM_COOKIE,  $params->date_from_str);
            $params->date_to_str        = get_cookie(DATE_TO_COOKIE,    $params->date_to_str);
            $params->country            = get_cookie(COUNTRY_COOKIE,    '');
            $params->category           = get_cookie(CATEGORY_COOKIE,   '');
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

            if (isset($_GET['country']) )
            {
                $params->country = $_GET['country'];
            }

            if (isset($_GET['categories']) )
            {
                $params->category = $_GET['categories'];
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
                $report = $reports_table->find($params->id);

                $params->reports = array($report);
            }
            else
            {
                $query_params                       = new ReportsQueryParams();

                $query_params->date_from            = $params->date_from_str;
                $query_params->date_to              = $params->date_to_str;
                $query_params->country              = $params->country;
                $query_params->category             = $params->category;
                $query_params->filter               = $params->filter;
                $query_params->status               = (is_editor_user() || is_admin_user() ) ? ReportStatus::draft | ReportStatus::published : ReportStatus::published;
                $query_params->sort_field           = $sort_column;
                $query_params->sort_ascending       = $sort_ascending;

                $params->reports                    = $reports_table->get_all($query_params);
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
         *  Show the "Draft reports" page.
         */
        public function drafts()
        {
            $params             = new reports_params();

            $params->view_as    = get_cookie(VIEW_AS_COOKIE,    'list');
            $params->filter     = get_cookie(FILTER_COOKIE,     '');

            if (isset($_GET['view']) )
            {
                $params->view_as = $_GET['view'];
            }

            if (isset($_GET['filter'])) 
            {
                $params->filter = $_GET['filter'];
            }

            require_once('views/reports/drafts.php');
        }


        /**
         *  Show the "Recent Updates" page.
         */
        public function recent()
        {
            $params             = new reports_params();

            $params->view_as    = get_cookie(VIEW_AS_COOKIE,    'list');
            $params->filter     = get_cookie(FILTER_COOKIE,     '');

            if (isset($_GET['view']) )
            {
                $params->view_as = $_GET['view'];
            }

            if (isset($_GET['filter']) )
            {
                $params->filter = $_GET['filter'];
            }

            require_once('views/reports/recent.php');
        }


        /**
         *  Show the current report.
         */
        public function show()
        {
            $id = self::get_current_id();

            // Raw urls are of the form ?controller=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $db                 = new db_credentials();
            $reports_table      = new Reports($db);

            $report             = $reports_table->find($id);

            // Check that the invoked URL is the correct one - if not redirect to it.
            $current_link       = $_SERVER['REQUEST_URI'];
            $permalink          = get_permalink($report);

            if ($current_link != $permalink)
            {
                $url = raw_get_host().$permalink;

                if (redirect_to($url, 301) )
                {
                    exit;
                }
            }
            require_once('views/reports/show.php');
        }


        /**
         *  Add a report.
         */
        public function add()
        {
            $site_config        = get_config();

            $is_admin           = is_admin_user();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];
            $edits_disabled_msg = $site_config['Admin']['edits_disabled_message'];

            if ($edits_disabled && !$is_admin)
            {
                echo "<script>alert('$edits_disabled_msg')</script>";
                $this->index();

                return;
            }

            require_once('views/reports/add.php');
        }


        /**
         *  Edit the current report.
         */
        public function edit()
        {
            $site_config        = get_config();

            $is_admin           = is_admin_user();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];
            $edits_disabled_msg = $site_config['Admin']['edits_disabled_message'];

            if ($edits_disabled && !$is_admin)
            {
                echo "<script>alert('$edits_disabled_msg')</script>";
                $this->Show();

                return;
            }

            $id = self::get_current_id();

            // Raw urls are of the form ?controller=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $db                 = new db_credentials();
            $reports_table      = new Reports($db);

            $report             = $reports_table->find($id);

            require_once('views/reports/edit.php');
        }


        /**
         *  Publish the current report.
         */
        public function publish()
        {
            $site_config        = get_config();

            $is_admin           = is_admin_user();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];
            $edits_disabled_msg = $site_config['Admin']['edits_disabled_message'];

            if ($edits_disabled && !$is_admin)
            {
                echo "<script>alert('$edits_disabled_msg')</script>";
                $this->show();

                return;
            }

            $id = self::get_current_id();

            // Raw urls are of the form ?controller=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $db                     = new db_credentials();
            $reports_table          = new Reports($db);

            $report                 = $reports_table->find($id);

            $updated_report         = new Report();

            $updated_report->set_from_report($report);
            $updated_report->draft  = false;

            $changes                = Reports::get_changed_properties($updated_report, $report);

            if ($reports_table->update($updated_report) )
            {
                ReportEvents::report_updated($updated_report, $changes);

                redirect_to($report->permalink);
            }
        }


        /**
         *  Unpublish the current report.
         */
        public function unpublish()
        {
            $site_config        = get_config();

            $is_admin           = is_admin_user();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];
            $edits_disabled_msg = $site_config['Admin']['edits_disabled_message'];

            if ($edits_disabled && !$is_admin)
            {
                echo "<script>alert('$edits_disabled_msg')</script>";
                $this->show();

                return;
            }

            $id = self::get_current_id();

            // Raw urls are of the form ?controller=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $db                     = new db_credentials();
            $reports_table          = new Reports($db);

            $report                 = $reports_table->find($id);

            $updated_report         = new Report();

            $updated_report->set_from_report($report);
            $updated_report->draft  = true;

            $changes                = Reports::get_changed_properties($updated_report, $report);

            if ($reports_table->update($updated_report) )
            {
                ReportEvents::report_updated($updated_report, $changes);

                redirect_to($report->permalink);
            }
        }


        /**
         *  Update the thumbnail of the current report.
         */
        public function update_thumbnail()
        {
            $is_admin = is_admin_user();

            $id = self::get_current_id();

            if ($id == 0)
            {
                return call('pages', 'error');
            }

            $db = new db_credentials();
            $reports_table = new Reports($db);

            $report = $reports_table->find($id);

            require_once('views/reports/update_thumbnail.php');
        }


        /**
         *  Delete the current report.
         */
        public function delete()
        {
            $site_config        = get_config();

            $is_admin           = is_admin_user();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];
            $edits_disabled_msg = $site_config['Admin']['edits_disabled_message'];

            if ($edits_disabled && !$is_admin)
            {
                echo "<script>alert('$edits_disabled_msg')</script>";
                $this->show();

                return;
            }

            $id = self::get_current_id();

            // Raw urls are of the form ?controller=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $db                 = new db_credentials();
            $reports_table      = new Reports($db);

            $report             = $reports_table->find($id);

            require_once('views/reports/delete.php');
        }


        /**
         *  Undelete the current report.
         */
        public function undelete()
        {
            $site_config        = get_config();

            $is_admin           = is_admin_user();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];
            $edits_disabled_msg = $site_config['Admin']['edits_disabled_message'];

            if ($edits_disabled && !$is_admin)
            {
                echo "<script>alert('$edits_disabled_msg')</script>";
                $this->show();

                return;
            }

            $id = self::get_current_id();

            // Raw urls are of the form ?controller=reports&action=show&id=x
            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding report
            $db                         = new db_credentials();
            $reports_table              = new Reports($db);

            $report                     = $reports_table->find($id);

            $updated_report             = new Report();

            $updated_report->set_from_report($report);
            $updated_report->deleted    = false;

            $changes                    = Reports::get_changed_properties($updated_report, $report);

            if ($reports_table->update($updated_report) )
            {
                ReportEvents::report_updated($updated_report, $changes);

                redirect_to($report->permalink);
            }
        }



    }


?>
