<?php
    /**
     * Database credentials. Customise this file for your database installation.
     *
     */


    /**
     * Class to hold database credentials.
     *
     */
    class db_credentials
    {
        /** @var string                   The server name. */
        public $servername  = "localhost";

        /** @var string                   The database name. */
        public $dbname      = "tdor";

        /** @var string                   The database username. */
        public $username    = "tester";

        /** @var string                   The database password. */
        public $password    = "test";

        /** @var array                    The PDO options to apply to the connection (this is probably in the wrong place). */
        public $pdo_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    }

?>