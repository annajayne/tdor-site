<?php
    /**
     * Controller for blogposts
     *
     */
    require_once('models/blogposts.php');



    /**
     *  Controller for blogposts
     *
     *  Supported actions:
     *
     *      'index'     - Show a top level index page.
     *      'show'      - Show an individual blogpost.
     *      'add'       - Add a new blogpost.
     *      'edit'      - Edit an existing blogpost.
     *      'publish'   - Publish a existing blogpost.
     *      'unpublish' - Publish a existing blogpost.
     *      'delete'    - Delete an existing blogpost.
     *      'undelete'  - Undelete a deleted blogpost.
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
            return $action;
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
         * Open the 'index' page.
         *
         */
        public function index()
        {
            $db                 = new db_credentials();
            $blogposts_table    = new BlogPosts($db);

            $query_params = new BlogpostsQueryParams();

            if (is_admin_user() )
            {
                $query_params->include_drafts   = true;
                $query_params->include_deleted  = true;
            }

            $blogposts = $blogposts_table->get_all($query_params);

            if (DEV_INSTALL && empty($blogposts) )
            {
                $blogposts_table->add_dummy_data();

                $blogposts = $blogposts_table->get_all($query_params);
            }

            require_once('views/blog/index.php');
        }


        /**
         * Open the 'show' page.
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
            $blogposts_table    = new BlogPosts($db);

            $blogpost           = $blogposts_table->find($id);

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
            $blogposts_table    = new BlogPosts($db);

            $blogpost           = $blogposts_table->find($id);

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
            $blogposts_table    = new BlogPosts($db);

            $blogpost           = $blogposts_table->find($id);

            $blogpost->draft    = false;

            if ($blogposts_table->update_post($blogpost) )
            {
                //BlogEvents::blogpost_updated($blogpost);

                redirect_to($blogpost->permalink);
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
            $blogposts_table    = new BlogPosts($db);

            $blogpost           = $blogposts_table->find($id);

            $blogpost->draft    = true;

            if ($blogposts_table->update_post($blogpost) )
            {
                //BlogEvents::blogpost_updated($blogpost);

                redirect_to($blogpost->permalink);
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
            $blogposts_table    = new BlogPosts($db);

            $blogpost           = $blogposts_table->find($id);

            require_once('views/blog/delete.php');
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
            $blogposts_table    = new BlogPosts($db);

            $blogpost           = $blogposts_table->find($id);

            $blogpost->deleted  = false;
            $blogpost->draft    = true;

            if ($blogposts_table->update_post($blogpost) )
            {
                //BlogEvents::blogpost_updated($blogpost);

                redirect_to($blogpost->permalink);
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
                $blogposts_table    = new BlogPosts($db);

                $id                 = $blogposts_table->get_id_from_uid($uid);
            }

            if ( ($id === 0) && isset($_GET['id']) )
            {
                // Raw urls are of the form ?category=blog&action=show&id=x
                $id                 = $_GET['id'];
            }
            return $id;
        }
    
    
    }

?>
