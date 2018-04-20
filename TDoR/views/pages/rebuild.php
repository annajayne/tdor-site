<?php
    function local_escape_quotes($text)
    {
        return str_replace("'", "''", $text);
    }


    function add_dummy_data($db, $item)
    {
        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        $comma = ', ';

        $sql = 'INSERT INTO incidents (name, age, photo_filename, photo_source, date, tgeu_ref, location, country, cause, description) VALUES ('.
            single_quote($item->name).$comma.
            single_quote($item->age).$comma.
            single_quote($item->photo_filename).$comma.
            single_quote($item->photo_source).$comma.
            single_quote(date_str_to_utc($item->date) ).$comma.
            single_quote($item->tgeu_ref).$comma.
            single_quote($item->location).$comma.
            single_quote($item->country).$comma.
            single_quote($item->cause).$comma.
            single_quote(local_escape_quotes($item->description) ).')';

        $ok = FALSE;

        try
        {
            $ok = $conn->query($sql);
        }
        catch (Exception $e)
        {
            echo "Caught exception: $e->getMessage()\n";
        }

        if ($ok !== FALSE)
        {
            log_text("Record for $item->name added successfully");
        }
        else
        {
            log_error("<br>Error adding data: $conn->error");
            log_error("<br>SQL: $sql");
        }

        $conn = null;
    }


    function add_dummy_data_from_file($db, $pathname)
    {
        if (file_exists($pathname) )
        {
            log_text("Reading $pathname");

            $items = read_csv_file($pathname);

            foreach ($items as $item)
            {
                add_dummy_data($db, $item);
            }
        }
    }


    // Credentials and DB name are coded in db_credentials.php
    $db = new db_credentials();

    if (db_exists($db) )
    {
        log_text('Dropping database...');
        drop_db($db);
    }

    // If the database doesn't exist, create it and add some dummy data
    if (!db_exists($db) )
    {
        log_text('Creating database...');
        create_db($db);
    }

    if (!table_exists($db, 'incidents') )
    {
        log_text('Adding tables...');
        add_tables($db);

        log_text('Adding dummy data...');

        add_dummy_data_from_file($db, 'data/tdor_2018_01.csv');
        add_dummy_data_from_file($db, 'data/tdor_2018_02.csv');
        add_dummy_data_from_file($db, 'data/tdor_2018_03.csv');
    }
?>

<p>Database rebuilt.</p>
