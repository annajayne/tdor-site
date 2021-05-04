<?php
    /**
     * Controller base class.
     *
     */


    /**
     * Controller base class.
     *
     */
    abstract class Controller
    {
        /**
         * Return the name of the controller
         *
         * @return string                                   The name of the controller.
         */
        abstract public function get_name();


       /**
         * Return the names of the supported actions
         *
         * @return array                                    An array of the names of the actions supported by this controller.
         */
        abstract public function get_actions();


        /**
         * Get the appropriate title for the specified action on the controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page title.
         */
        abstract function get_page_title($action);


        /**
         * Get the appropriate description for the specified action on the given controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page description.
         */
        abstract function get_page_description($action);


        /**
         * Get the appropriate keywords for the specified action on the controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page keywords.
         */
        abstract function get_page_keywords($action);


        /**
         * Get the appropriate thumbnail for the specified action on the controller.
         *
         * @param string $action            The name of the action.
         * @return string                   The page keywords.
         */
        function get_page_thumbnail($action)
        {
            return append_path(raw_get_host(), '/images/tdor_candle_jars.jpg');
        }


    }


?>