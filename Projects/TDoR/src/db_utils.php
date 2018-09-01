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
     * Add the users table.
     *
     * @param db_credentials $db                  The properties of the connection.
     */
    function add_users_table($db)
    {
        $table_name = 'users';

        log_text("Adding table $table_name...");

        $conn = get_connection($db);

        $sql = "CREATE TABLE $table_name (id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                            username VARCHAR(50) NOT NULL UNIQUE,
                                            password VARCHAR(255) NOT NULL,
                                            activated INT NOT NULL,
                                            created_at DATETIME)";

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


    /**
     * Add the reports table.
     *
     * @param db_credentials $db                  The properties of the connection.
     * @param string $table_name                  The name of the table.
     */
    function add_reports_table($db, $table_name)
    {
        log_text("Adding table $table_name...");

        $conn = get_connection($db);

        $sql = "CREATE TABLE $table_name (id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                                    uid VARCHAR(8),
                                    deleted BOOL NOT NULL,
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
                                    permalink VARCHAR(255),
                                    date_created DATE,
                                    date_updated DATE)";

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