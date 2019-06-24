<?php
     /**
     * JSON implementation file.
     * 
     */
    require_once('./json_schema.php');
    require_once('./json_parameters.php');
    require_once('./json_status.php');
    require_once('./json_data.php');
    require_once('./json_report_data.php');
    require_once('./json_reports_data.php');
    

     /**
     * Top level JSON response class.
     * 
     * An instance of this class is encoded by the API to return the requested details.
     *
     */
    class JsonReponse
    {
        /** @var integer                A JsonStatus object which gives the version of the JSON API response. */
        public $schema          = null;

        /** @var JsonStatus             A JsonStatus object which gives details of the status of the API call. */
        public $status        	= null;

        /** @var JsonParameters         A JsonParameters object which gives details of the parameters passed to the API call. */
        public $parameters    	= null;

        /** @var JsonData               The JSON data corresponding to the given query */
        public $data            = null;

        
        public function __construct()
        {
            $this->schema 		= new JsonSchema();
        }
        
    }


?>