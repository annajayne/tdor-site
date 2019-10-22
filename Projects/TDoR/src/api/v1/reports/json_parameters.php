<?php
     /**
     * JSON implementation file.
     * 
     */


    /**
     * Class to represent the parameters given to a JSON API call.
     * 
     * Note that only the names of valid parameters are returned.
     *
     */
    class JsonParameters
    {
        /** @var string                  The API key of the user. */
        public $api_key         = '';

        /** @var string                  The earliest date for which report data should be returned. */
        public $date_from       = '';

        /** @var string                  The latest date for which report data should be returned. */
        public $date_to         = '';

        /** @var string                  The name of the country for which report data should be returned. */
        public $country         = '';

        /** @var string                  An arbitrary filter string which should be applied to the report data to be returned. */
        public $filter          = '';


        /** @var string                  The URL of a single report for which data should be returned. */
        public $url             = '';

        /** @var string                  The UID of a single report for which data should be returned. */
        public $uid             = '';


    }


?>