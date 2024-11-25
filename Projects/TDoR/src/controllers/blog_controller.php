<?php
    /**
     * Controller for blogposts
     *
     */
    require_once('util/string_utils.php');              // For is_valid_hex_string()
    require_once('util/path_utils.php');                // For append_path()
    require_once('models/blog_table.php');
    require_once('models/blog_events.php');


    /**
     * Return the dates bounding the date encoded in the given path (e.g. <host>/reports/year/month/day/title_uid)
     *
     * @param string $path                        A URL encoding the specified date.
     * @return array                              An array containing the start and end dates bounding the given year, month and day, in ISO format.
     */
    function get_date_range_from_blogpost_url($path)
    {
        $range = array();

        if (ENABLE_FRIENDLY_URLS)
        {
            $elements = explode('/', $path);                // Split path on slashes

            // e.g. <host>/blog/year/month/day/title_uid
            $element_count = count($elements);

            if ( ($element_count >= 1) && ($elements[0] == 'blog') )
            {
                $year       = 0;
                $month      = 0;
                $day        = 0;

                if ($element_count >= 2)
                {
                    $year = intval($elements[1]);
                }
                if ($element_count >= 3)
                {
                    $month = intval($elements[2]);
                }
                if ($element_count >= 4)
                {
                    $day = intval($elements[3]);
                }

                if ($year > 0)
                {
                    $range = get_date_range_from_year_month_day($year, $month, $day);
                }
            }
        }
        return $range;
    }


    /**
     *  Controller for blogposts
     *
     *  Supported actions:
     *
     *      'index'     - Show the main blog page.
     *      'show'      - Show an individual blogpost.
     *      'add'       - Add a new blogpost.
     *      'edit'      - Edit an existing blogpost.
     *      'publish'   - Publish a existing blogpost.
     *      'unpublish' - Publish a existing blogpost.
     *      'delete'    - Delete an existing blogpost.
     *      'undelete'  - Undelete a deleted blogpost.
     *      'purge'     - Purge (permanently delete) an existing blogpost.
     */
    class BlogController
    {
        /**
         * Return the name of the controller
         *
         * @return string                                   The name of the controller.
         */
        public function get_name()
        {
            return 'blog';
        }


        /**
         * Return the names of the supported actions
         *
         * @return array                                    An array of the names of the actions supported by this controller.
         */
        public function get_actions()
        {
            return array('index',
                         'show',
                         'add',
                         'edit',
                         'publish',
                         'unpublish',
                         'delete',
                         'undelete',
                         'purge');
        }


        /**
         * Get the appropriate title for the given specified action on the given controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page title.
         */
        function get_page_title($action)
        {
            switch ($action)
            {
                case 'show';
                    $blogpost 	= $this->get_current_blogpost();

                    if ($blogpost)
                    {
                        $title	= $blogpost->title;
                    }
                    break;

                case 'add':
                    $title  = "Add Blogpost";

                    break;

                case 'edit';
                    $title  = "Edit Blogpost";
                    break;

                default:
                    $title 		= 'Remembering Our Dead - Blog';
                    break;
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
            $blogpost = $this->get_current_blogpost();

            if ($blogpost)
            {
                return $blogpost->get_subtitle();
            }
            return $this->get_page_title($action);
        }


        /**
         * Get the appropriate keywords for the specified action on the controller.
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
                    $blogpost = $this->get_current_blogpost();

                    if ($blogpost)
                    {
                        $thumbnail = $blogpost->thumbnail_filename;
                    }
                    break;

                default:
                    break;
            }
            return append_path(raw_get_host(), $thumbnail);
        }


        /**
         * Show the main 'Blog' page
         *
         */
        public function index()
        {
            $db             = new db_credentials();
            $blog_table     = new BlogTable($db);

            $query_params   = new BlogTableQueryParams();

            $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)

            $range = get_date_range_from_blogpost_url($path);

            if (count($range) === 2)
            {
                if (!empty($range[0]) && !empty($range[1]) )
                {
                    $query_params->date_from    = $range[0];    // Start date
                    $query_params->date_to      = $range[1];    // End date
                }
            }

            if (is_admin_user() )
            {
                $query_params->include_drafts   = true;
                $query_params->include_deleted  = true;
            }

            $blogposts = $blog_table->get_all($query_params);

            require_once('views/blog/index.php');
        }


        /**
         * Show a specific blogpost.
         *
         */
        public function show()
        {
            $id = $this->get_current_id();

            // If we don't have an id we just redirect to the error page as we need the blogpost id to find it in the database
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding blogpost
            $db                 = new db_credentials();
            $blog_table         = new BlogTable($db);

            $blogpost           = $blog_table->find($id);

            $requested_url      = $_SERVER['REQUEST_URI'];

            // Check that the invoked URL is the correct one - if not redirect to it.
            if ($requested_url != $blogpost->permalink)
            {
                $url = raw_get_host().$blogpost->permalink;

                if (redirect_to($url /*, 301*/) )
                {
                    exit;
                }
            }
            require_once('views/blog/show.php');
        }


        /**
         *  Add a new blogpost.
         */
        public function add()
        {
            require_once('views/blog/add.php');
        }


        /**
         *  Edit the current blogpost.
         */
        public function edit()
        {
            $id = $this->get_current_id();

            // If we don't have an id we just redirect to the error page as we need the blogpost id to find it in the database
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding blogpost
            $db                 = new db_credentials();
            $blog_table         = new BlogTable($db);

            $blogpost           = $blog_table->find($id);

            require_once('views/blog/edit.php');
        }


        /**
         *  Publish a draft blogpost.
         */
        public function publish()
        {
            $id = $this->get_current_id();

            // If we don't have an id we just redirect to the error page as we need the blogpost id to find it in the database
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding blogpost
            $db                 = new db_credentials();
            $blog_table         = new BlogTable($db);

            $blogpost           = $blog_table->find($id);

            $blogpost->draft    = false;

            if ($blog_table->update($blogpost) )
            {
                BlogEvents::blogpost_updated($blogpost);

                $referrer = $blogpost->permalink;

                if (isset($_SERVER['HTTP_REFERER']) )
                {
                    $referrer = $_SERVER['HTTP_REFERER'];
                }
                redirect_to($referrer);
            }
        }


        /**
         *  Unpublish a blogpost.
         */
        public function unpublish()
        {
            $id = $this->get_current_id();

            // If we don't have an id we just redirect to the error page as we need the blogpost id to find it in the database
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding blogpost
            $db                 = new db_credentials();
            $blog_table         = new BlogTable($db);

            $blogpost           = $blog_table->find($id);

            $blogpost->draft    = true;

            if ($blog_table->update($blogpost) )
            {
                BlogEvents::blogpost_updated($blogpost);

                $referrer = $blogpost->permalink;

                if (isset($_SERVER['HTTP_REFERER']) )
                {
                    $referrer = $_SERVER['HTTP_REFERER'];
                }
                redirect_to($referrer);
            }
        }


        /**
         *  Delete the current blogpost.
         */
        public function delete()
        {
            $id = $this->get_current_id();

            // If we don't have an id we just redirect to the error page as we need the blogpost id to find it in the database
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding blogpost
            $db                 = new db_credentials();
            $blog_table         = new BlogTable($db);

            $blogpost           = $blog_table->find($id);

            require_once('views/blog/delete.php');

            if ($blogpost->deleted)
            {
                BlogEvents::blogpost_deleted($blogpost);
            }

            if (isset($_SERVER['HTTP_REFERER']) )
            {
                $referrer = $_SERVER['HTTP_REFERER'];

                if (!empty($referrer) )
                {
                    redirect_to($referrer);
                }
            }
        }


        /**
         *  Purge the current blogpost.
         */
        public function purge()
        {
            $id = $this->get_current_id();

            // If we don't have an id we just redirect to the error page as we need the blogpost id to find it in the database
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding blogpost
            $db                 = new db_credentials();
            $blog_table         = new BlogTable($db);

            $blogpost           = $blog_table->find($id);

            require_once('views/blog/purge.php');

            if (!$blog_table->find($id) )
            {
                BlogEvents::blogpost_purged($blogpost);
            }

            if (isset($_SERVER['HTTP_REFERER']) )
            {
                $referrer = $_SERVER['HTTP_REFERER'];

                if (!empty($referrer) )
                {
                    redirect_to($referrer);
                }
            }
        }


        /**
         *  Undelete a blogpost.
         */
        public function undelete()
        {
            $id = $this->get_current_id();

            // If we don't have an id we just redirect to the error page as we need the blogpost id to find it in the database
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding blogpost
            $db                 = new db_credentials();
            $blog_table    = new BlogTable($db);

            $blogpost           = $blog_table->find($id);

            $blogpost->deleted  = false;
            $blogpost->draft    = true;

            if ($blog_table->update($blogpost) )
            {
                BlogEvents::blogpost_updated($blogpost);

                $referrer = $blogpost->permalink;

                if (isset($_SERVER['HTTP_REFERER']) )
                {
                    $referrer = $_SERVER['HTTP_REFERER'];
                }
                redirect_to($referrer);
            }
        }


        /**
         *  Get the id of the blogpost to display from the current URL.
         *
         *  The id may be encoded as either an id (integer) or uid (hex string).
         *
         *  @return int                   The id of the blogpost to display.
         */
        private function get_current_id()
        {
            $id                     = 0;
            $uid                    = '';

            if (ENABLE_FRIENDLY_URLS)
            {
                $path               = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
                $uid                = get_uid_from_friendly_url($path);
            }

            if (empty($uid) && isset($_GET['uid']) )
            {
                $uid                = $_GET['uid'];
            }

            // Validate
            if (!empty($uid) && is_valid_hex_string($uid) )
            {
                $db                 = new db_credentials();
                $blog_table    = new BlogTable($db);

                $id                 = $blog_table->get_id_from_uid($uid);
            }

            if ( ($id === 0) && isset($_GET['id']) )
            {
                // Raw urls are of the form ?category=blog&action=show&id=x
                $id                 = $_GET['id'];
            }
            return $id;
        }


        /**
         *  Get the blogpost to display from the current URL.
         *
         *  @return Blogpost              The blogpost to display.
         */
        public function get_current_blogpost()
        {
            $id = $this->get_current_id();

            if ($id > 0)
            {
                $db         = new db_credentials();
                $blog_table = new BlogTable($db);

                $blogpost   = $blog_table->find($id);

                return $blogpost;
            }
            return null;
        }

    }

?>
