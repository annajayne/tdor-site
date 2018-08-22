<?php
    /**
     * Database connection implementation.
     *
     */


   /**
     * Database connection class.
     *
     */
    class Db
    {
        /** @var resource           The database instance. */
        private static $instance = NULL;


        private function __construct()
        {
        }


        private function __clone()
        {
        }


        /**
         * Get an instance of the database connection. W
         *
         * NB: UGH - Singleton. See https://stackoverflow.com/questions/4595964/is-there-a-use-case-for-singletons-with-database-access-in-php for alternative approaches.
         *
         * @return resource             A database connection instance.
         */
        public static function getInstance()
        {
            if (!isset(self::$instance) )
            {
                $db = new db_credentials();

                self::$instance = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);
            }
            return self::$instance;
        }
    }

    require_once('db_credentials.php');

?>