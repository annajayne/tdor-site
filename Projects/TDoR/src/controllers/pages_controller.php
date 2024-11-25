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
     *      'home'      - Show the homepage.
     *      'api'       - Show the "API" page.
     *      'downloads' - Show the "Downloads" page.
     *      'search'    - Show the "Search" page.
     *      'stats'     - Show the "Statistics" page.
     *      'about'     - Show the "About" page.
     *      'contact'   - Show the "Contact" page.
     *      'admin'     - Show the "Admin" pages.
     *      'error'     - Show the "Error" page.
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
                          'downloads',
                          'search',
                          'stats',
                          'api',
                          'about',
                          'contact',
                          'admin',
                          'error');
        }


        /**
         * Get the appropriate title for the specified action on the controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page title.
         */
        function get_page_title($action)
        {
           $title = 'Remembering Our Dead';

           $titles = array('home' =>       '',
                            'downloads' =>  'Downloads',
                            'search' =>     'Search',
                            'stats' =>      'Statistics',
                            'api' =>        'API',
                            'about' =>      'About',
                            'contact' =>    'Contact',
                            'admin' =>      'Admin',
                            'error' =>      'Error');

           if (!empty($titles[$action]) )
           {
               $title = $title.' - '.$titles[$action];
           }
           return $title;
        }


        /**
         * Get the appropriate description for the specified action on the controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page description.
         */
        function get_page_description($action)
        {
            $description = 'This site memorialises trans people who have passed away, as a supporting resource for the Trans Day of Remembrance (TDoR).';

            switch ($action)
            {
                case 'home':
                    break;

                default:
                    break;
            }
            return $description;
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
         * Show the "Downloads" page.
         *
         */
        public function downloads()
        {
            require_once('views/pages/downloads.php');
        }


        /**
         * Show the "Statistics" page.
         *
         */
        public function stats()
        {
            require_once('views/pages/stats.php');
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