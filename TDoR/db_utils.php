<?php

    require_once('db_credentials.php');


    function get_connection($db)
    {
        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        return $conn;
    }


    function db_exists($db)
    {
        $db_exists = false;

        try
        {
            $conn = get_connection($db);

            $db_exists = true;

            log_text('Database exists<br>');
        }
        catch (PDOException $e)
        {
            log_text("Database $db->dbname does not exist on $db->servername. $e->getMessage<br>");
        }

        $conn = null;

        return $db_exists;
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
        $conn = get_connection($db);

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

        $conn = get_connection($db);

        $sql = "SHOW TABLES LIKE '$table_name'";

        $result = $conn->query($sql);

        $rows = $result->fetchAll();

        if (count($rows) > 0)
        {
            $table_exists = true;
            log_text("Table '$table_name' exists<br>");
        }
        else
        {
            log_text("Table '$table_name' does not exist<br>");
        }

        $conn = null;

        return $table_exists;
    }


    function drop_table($db, $table_name)
    {
        $conn = get_connection($db);

        $sql = "DROP TABLE ".$table_name;

        if ($conn->query($sql) !== FALSE)
        {
            log_text("Table $table_name dropped");
        }
        else
        {
            log_error("Error dropping table $table_name: " . $conn->error);
        }

        $conn = null;
    }


    function add_tables($db)
    {
        $table_name = 'reports';

        log_text("Adding table $table_name...");

        $conn = get_connection($db);

        $sql = "CREATE TABLE $table_name (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    name VARCHAR(255) NOT NULL,
                                    age VARCHAR(30),
                                    photo_filename VARCHAR(255),
                                    photo_source VARCHAR(255),
                                    date DATE NOT NULL,
                                    tgeu_ref VARCHAR(255),
                                    location VARCHAR(255) NOT NULL,
                                    country VARCHAR(255) NOT NULL,
                                    cause VARCHAR(255),
                                    description TEXT,
                                    description_html TEXT)";

        if ($conn->query($sql) !== FALSE)
        {
            log_text("Table $table_name created successfully");
        }
        else
        {
            log_error("Error creating table $table_name: " . $conn->error);
        }

        $conn = null;
    }

?>