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
            $db = new db_credentials();

            $posts_table = new Posts($db);

            $posts = $posts_table->get_all();

            require_once('views/posts/index.php');
        }


        /**
         * Open the 'show' page.
         *
         */
        public function show()
        {
            // We expect a url of the form ?controller=posts&action=show&id=x
            // (without an id we just redirect to the error page as we need the post id to find it in the database)
            if (!isset($_GET['id']) )
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding post
            $db = new db_credentials();

            $posts_table = new Posts($db);

            $post = $posts_table->find($_GET['id']);

            require_once('views/posts/show.php');
        }
    }


?>