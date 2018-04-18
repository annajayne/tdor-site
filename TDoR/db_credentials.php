<?php

    class db_credentials
    {
        public $servername  = "localhost";
        public $dbname      = "tdor";
        public $username    = "tester";
        public $password    = "test";

        public $pdo_options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    }

?>