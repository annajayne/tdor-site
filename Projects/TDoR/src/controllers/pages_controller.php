<?php
    /**
     * PagesController implementation.
     *
     */


    /**
     * Controller for static pages.
     *
     * Actions supported:
     *
     *      'home'    - Show the homepage.
     *      'api'     - Show the "API" page.
     *      'search'  - Show the "Search" page.
     *      'about'   - Show the "About" page.
     *      'contact' - Show the "Contact" page.
     *      'admin'   - Show the "Admin" pages.
     *      'error'   - Show the "Error" page.
     */
    class PagesController extends Controller
    {
        /**
         * Return the name of the controller
         *
         * @return string                                   The name of the controller.
         */
        public function get_name()
        {
            return 'pages';
        }


        /**
         * Return the names of the supported actions
         *
         * @return array                                    An array of the names of the actions supported by this controller.
         */
        public function get_actions()
        {
            return array('home',
                          'search',
                          'api',
                          'about',
                          'contact',
                          'admin',
                          'error');
        }


        /**
         * Get the appropriate title for the given specified action on the given controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page title.
         */
        function get_page_title($action)
        {
           $title = '';

           $titles = array('home' =>       '',
                            'search' =>     'Search',
                            'api' =>        'API',
                            'about' =>      'About',
                            'contact' =>    'Contact',
                            'admin' =>      'Admin',
                            'error' =>      'Error');

           if (!empty($titles[$action]) )
           {
               $title = $titles[$action];
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
         * Show the homepage.
         *
         */
        public function home()
        {
            require_once('views/pages/home.php');
        }


        /**
         * Show the "Search" page.
         *
         */
        public function search()
        {
            require_once('views/pages/search.php');
        }


        /**
         * Show the "API" page.
         *
         */
        public function api()
        {
            require_once('views/pages/api.php');
        }


        /**
         * Show the "About" page.
         *
         */
        public function about()
        {
            require_once('views/pages/about.php');
        }


        /**
         * Show the "Contact" page.
         *
         */
        public function contact()
        {
            require_once('views/pages/contact.php');
        }


        /**
         * Show the "Admin" page.
         *
         */
        public function admin()
        {
            require_once('views/pages/admin.php');
        }


        /**
         * Show the "Error" page.
         *
         */
        public function error()
        {
            require_once('views/pages/error.php');
        }
    }
?>