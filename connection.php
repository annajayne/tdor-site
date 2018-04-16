<?php
    // DB connection class
    class Db
    {
        private static $instance = NULL;


        private function __construct()
        {
        }


        private function __clone()
        {
        }


        // UGH: Singleton. See https://stackoverflow.com/questions/4595964/is-there-a-use-case-for-singletons-with-database-access-in-php for alternative approaches.
        public static function getInstance()
        {
            if (!isset(self::$instance) )
            {
                $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
                self::$instance = new PDO('mysql:host=localhost;dbname=php_mvc', 'root', '', $pdo_options);
            }
            return self::$instance;
        }
    }
?>