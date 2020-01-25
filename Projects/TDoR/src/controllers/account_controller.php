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
     *      'register'              - Show the "Register" page.
     *      'confirm_registration'  - Show the "Confirm Registration" page.
     *      'login'                 - Show the "Login" page.
     *      'welcome'               - Show the "Welcome" (i.e. account) page.
     *      'change_password'       - Show the "Change password" page.
     *      'logout'                - Show the "Logout" page.
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
                         'change_password',
                         'register',
                         'confirm_registration',
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

           $titles = array('login' =>                   'Login',
                           'logout' =>                  'Logout',
                           'change_password' =>         'Change Password',
                           'register' =>                'Register',
                           'confirm_registration' =>    'Confirm Registration',
                           'welcome' =>                 'Account');

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
         * Show the "Change Password" page.
         *
         */
        public function change_password()
        {
            require_once('views/account/change_password.php');
        }


        /**
         * Show the "Register" page.
         *
         */
        public function register()
        {
            require_once('views/account/register.php');
        }


        /**
         * Show the "Register" page.
         *
         */
        public function confirm_registration()
        {
            require_once('views/account/confirm_registration.php');
        }


        /**
         * Show the "Account" page.
         *
         */
        public function welcome()
        {
            require_once('views/account/welcome.php');
        }



    }
?>