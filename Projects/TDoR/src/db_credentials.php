<?php
    /**
     * Database credentials config class
     *
     */
    require_once('utils.php');              // For get_config()



    /**
     * Class to hold database credentials.
     *
     */
    class db_credentials
    {
        /** @var string                   The server name. */
        public $servername      = "";

        /** @var string                   The database name. */
        public $dbname          = "";

        /** @var string                   The database username. */
        public $username        = "";

        /** @var string                   The database password. */
        public $password        = "";



        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $site_config        = get_config();

            $db_config          = $site_config['Database'];

            $this->servername   = $db_config['server_name'];
            $this->dbname       = $db_config['database_name'];
            $this->username     = $db_config['user_name'];
            $this->password     = $db_config['password'];
        }
    }

?>