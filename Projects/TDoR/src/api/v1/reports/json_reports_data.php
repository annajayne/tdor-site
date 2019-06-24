<?php
    /**
     * JSON implementation file.
     *
     */




     /**
     * JSON data class for a collection of reports.
     *
     */
    class JsonReportsData implements JsonData
    {
        /** @var integer                The number of reports returned. */
        public $reports_count   = 0;

        /** @var array                  An array of data on reports. */
        public $reports         = array();

    }



?>