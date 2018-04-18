<?php
    // Controller for static pages.
    //
    // Actions supported:
    //
    //      'home'
    //      'error'
    class PagesController
    {
        public function home()
        {
            require_once('views/pages/home.php');
        }


        public function error()
        {
            require_once('views/pages/error.php');
        }
    }
?>