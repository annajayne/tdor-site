<?php
    /**
     * Database utility functions
     *
     */


    require_once('db_credentials.php');


    /**
     * Get a connection to the database.
     *
     * @param db_credentials $db                  The properties of the connection.
     * @return PDO                                The database connection.
     */
    function get_connection($db)
    {
        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        return $conn;
    }


    /**
     * Determine if the database exists.
     *
     * @param db_credentials $db                  The properties of the connection.
     * @return boolean                            True if the database exists; false otherwise.
     */
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


    /**
     * Create the database.
     *
     * @param db_credentials $db                  The properties of the connection.
     */
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


    /**
     * Drop the database.
     *
     * @param db_credentials $db                  The properties of the connection.
     */
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


    /**
     * Determine if the specified table exists.
     *
     * @param db_credentials $db                  The properties of the connection.
     * @param string $table_name                  The name of the table.
     * @return boolean                            true if the table exists; false otherwise.
     */
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


    /**
     * Determine if the specified column exists.
     *
     * @param db_credentials $db                  The properties of the connection.
     * @param string $table_name                  The name of the table.
     * @param string $column_name                 The name of the column.
     * @return boolean                            true if the colun exists; false otherwise.
     */
    function column_exists($db, $table_name, $column_name)
    {
        $column_exists = false;

        if (table_exists($db, $table_name) )
        {
            $conn = get_connection($db);

            $sql = "SHOW COLUMNS FROM `$table_name` LIKE '$column_name'";

            $result = $conn->query($sql);

            $rows = $result->fetchAll();

            if (count($rows) > 0)
            {
                $column_exists = true;
                log_text("Table '$table_name' exists<br>");
            }
            else
            {
                log_text("Table '$table_name' does not exist<br>");
            }

            $conn = null;
        }
        return $column_exists;
    }


    /**
     * Drop the specified table.
     *
     * @param db_credentials $db                  The properties of the connection.
     * @param string $table_name                  The name of the table.
     */
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


    /**
     * Rename the specified table.
     *
     * @param db_credentials $db                  The properties of the connection.
     * @param string $existing_table_name         The name of the existing table.
     * @param string $new_table_name              The new name of the table.
     */
    function rename_table($db, $existing_table_name, $new_table_name)
    {
        $conn = get_connection($db);

        $sql = "RENAME TABLE $existing_table_name TO $new_table_name";

        if ($conn->query($sql) !== FALSE)
        {
            log_text("Table $existing_table_name renamed as $new_table_name");
        }
        else
        {
            log_error("Error renaming table $existing_table_name as $new_table_name: " . $conn->error);
        }

        $conn = null;
    }


    /**
     * Return an array of the names of the backup tables in the database.
     *
     * @param db_credentials $db                The properties of the connection.
     * @return array                            An array of the names of the backup tables.
     */
    function get_reports_backup_table_names($db)
    {
        $table_names = array();

        $conn = get_connection($db);

        $sql = "SHOW TABLES LIKE 'reports_backup%'";

        $result = $conn->query($sql);

        foreach ($result->fetchAll() as $row)
        {
            $table_names[] = $row[0];
        }

        $conn = null;

        return $table_names;
    }


?>