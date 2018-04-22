<?php

    require_once('db_credentials.php');


    function add_tables($db)
    {
        log_text("Adding table 'incidents'...");

        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        $sql = "CREATE TABLE incidents (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    name VARCHAR(255) NOT NULL,
                                    age VARCHAR(30),
                                    photo_filename VARCHAR(255),
                                    photo_source VARCHAR(255),
                                    date DATE NOT NULL,
                                    tgeu_ref VARCHAR(255),
                                    location VARCHAR(255) NOT NULL,
                                    country VARCHAR(255) NOT NULL,
                                    cause VARCHAR(255),
                                    description TEXT NOT NULL)";

        if ($conn->query($sql) !== FALSE)
        {
            log_text("Table incidents created successfully");
        }
        else
        {
            log_error("Error creating table: " . $conn->error);
        }

        $conn = null;
    }


    function single_quote($var)
    {
        return "'".$var."'";
    }


    function escape_quotes($var)
    {
        return str_replace("'", "''", $var);
    }


    function create_db($db)
    {
        // Connect to the MySQL server
        $db_created = false;

        try
        {
            echo("Attempting to create database $db->dbname<br>");

            $conn = new PDO("mysql:host=$db->servername", $db->username, $db->password);

            $conn->exec("CREATE DATABASE `$db->dbname`;") or die(print_r($conn->errorInfo(), true) );

            $db_created = true;
        }
        catch (PDOException $e)
        {
            echo("Error creating database $db->dbname : $e->getMessage()<br>");
        }

        if ($db_created)
        {
            echo "Database ".$db->dbname." successfully created<br>";
        }

        $conn = null;
    }


    function drop_db($db)
    {
        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        $sql = "DROP database ".$db->dbname;

        if ($conn->query($sql) !== FALSE)
        {
            log_text("Database dropped");
        }
        else
        {
            log_error("Error dropping database: " . $conn->error);
        }

        $conn = null;
    }


    function table_exists($db, $table_name)
    {
        $table_exists = false;

        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        $sql = "SHOW TABLES LIKE '$table_name'";

        $result = $conn->query($sql);

        $rows = $result->fetchAll();

        if (count($rows) > 0)
        {
            $table_exists = true;
            //echo "Table '$table_name' exists<br>";
        }
        else
        {
            echo "Table '$table_name' does not exist<br>";
        }

        $conn = null;

        return $table_exists;
    }


    function db_exists($db)
    {
        $db_exists = false;

        try
        {
            $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

            $db_exists = true;

           // echo 'Database exists<br>';
        }
        catch (PDOException $e)
        {
            echo("Database $db->dbname does not exist on $db->servername. $e->getMessage<br>");
        }

        $conn = null;

        return $db_exists;
    }

?>