<?php
    /**
     * AccountController implementation.
     *
     */


    /**
     * Controller for "account" pages.
     *
     * Actions supported:
     *
     *      'login'     - Show the "Login" page.
     *      'logout'    - Show the "Logout" pages.
     *      'register'  - Show the "Register" page.
     *      'welcome'   - Show the "Welcome" (i.e. account) page.
     */
    class AccountController extends Controller
    {
        /**
         * Return the name of the controller
         *
         * @return string                                   The name of the controller.
         */
        public function get_name()
        {
            return 'account';
        }


        /**
         * Return the names of the supported actions
         *
         * @return array                                    An array of the names of the actions supported by this controller.
         */
        public function get_actions()
        {
            return array('login',
                         'logout',
                         'register',
                         'welcome');
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

           $titles = array('login' =>       'Login',
                           'logout' =>      'Logout',
                           'register' =>    'Register',
                           'welcome' =>     'Account');

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
         * Show the "Login" page.
         *
         */
        public function login()
        {
            require_once('views/account/login.php');
        }


        /**
         * Show the "Logout" page.
         *
         */
        public function logout()
        {
            require_once('views/account/logout.php');
        }


        /**
         * Show the "Login" page.
         *
         */
        public function register()
        {
            require_once('views/account/register.php');
        }


        /**
         * Show the "Login" page.
         *
         */
        public function welcome()
        {
            require_once('views/account/welcome.php');
        }



    }
?>