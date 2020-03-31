<?php
    /**
     * Controller for posts (database pages).
     *
     *  Actions supported:
     *
     *      'index'
     *      'show'
     */
    require_once('models/posts.php');



    class PostsController
    {
        /**
         * Return the name of the controller
         *
         * @return string                                   The name of the controller.
         */
        public function get_name()
        {
            return 'posts';
        }


        /**
         * Return the names of the supported actions
         *
         * @return array                                    An array of the names of the actions supported by this controller.
         */
        public function get_actions()
        {
            return array('index', 'show');
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
            // Store all the posts in a variable
            $db             = new db_credentials();
            $posts_table    = new Posts($db);

            if (DEV_INSTALL)
            {
                $posts_table->add_dummy_data();
            }

            $posts = $posts_table->get_all();

            require_once('views/posts/index.php');
        }


        /**
         * Open the 'show' page.
         *
         */
        public function show()
        {
            $id = $this->get_current_id();

            // (without an id we just redirect to the error page as we need the report id to find it in the database)
            if ($id == 0)
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding post
            $db             = new db_credentials();
            $posts_table    = new Posts($db);

            $post           = $posts_table->find($id);

            $requested_url  = $_SERVER['REQUEST_URI'];

            // Check that the invoked URL is the correct one - if not redirect to it.
            if ($requested_url != $post->permalink)
            {
                $url = raw_get_host().$post->permalink;

                if (redirect_to($url /*, 301*/) )
                {
                    exit;
                }
            }
            require_once('views/posts/show.php');
        }
    
    
        /**
         *  Get the id of the post to display from the current URL.
         *
         *  The id may be encoded as either an id (integer) or uid (hex string).
         *
         *  @return int                   The id of the report to display.
         */
        private function get_current_id()
        {
            $id                 = 0;
            $uid                = '';

            if (ENABLE_FRIENDLY_URLS)
            {
                $path           = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
                $uid            = get_uid_from_friendly_url($path);
            }

            if (empty($uid) && isset($_GET['uid']) )
            {
                $uid            = $_GET['uid'];
            }

            // Validate
            if (!empty($uid) && is_valid_hex_string($uid) )
            {
                $db             = new db_credentials();
                $posts_table    = new Posts($db);

                $id             = $posts_table->get_id_from_uid($uid);
            }

            if ( ($id === 0) && isset($_GET['id']) )
            {
                // Raw urls are of the form ?category=posts&action=show&id=x
                $id             = $_GET['id'];
            }
            return $id;
        }
    
    
    }

?>
