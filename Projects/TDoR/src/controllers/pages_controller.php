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
     *      'search'  - Show the "Search" page.
     *      'about'   - Show the "About" page.
     *      'admin'   - Show the "Admin" pages.
     *      'api'     - Show the "API" page.
     *      'error'   - Show the "Error" page.
     */
    class PagesController
    {
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
         * Show the "About" page.
         *
         */
        public function about()
        {
            require_once('views/pages/about.php');
        }


        /**
         * Show the "Admin" page.
         *
         */
        public function admin()
        {
            require_once('views/pages/admin.php');
        }


        public function api()
        {
            require_once('views/pages/api.php');
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