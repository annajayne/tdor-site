<?php
    function add_data($db, $item)
    {
        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        $comma = ', ';

        $sql = 'INSERT INTO reports (name, age, photo_filename, photo_source, date, tgeu_ref, location, country, cause, description, description_html) VALUES ('.
            $conn->quote($item->name).$comma.
            $conn->quote($item->age).$comma.
            $conn->quote($item->photo_filename).$comma.
            $conn->quote($item->photo_source).$comma.
            $conn->quote(date_str_to_utc($item->date) ).$comma.
            $conn->quote($item->tgeu_ref).$comma.
            $conn->quote($item->location).$comma.
            $conn->quote($item->country).$comma.
            $conn->quote($item->cause).$comma.
            $conn->quote($item->description).$comma.
            $conn->quote($item->description_html).')';

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


    function add_data_from_file($db, $pathname)
    {
        if (file_exists($pathname) )
        {
            log_text("Reading $pathname");

            $items = read_csv_file($pathname);

            foreach ($items as $item)
            {
                echo "&nbsp;&nbsp;Adding record $item->date / $item->name / $item->location ($item->country)<br>";

                add_data($db, $item);
            }
        }
    }


    function extract_zipfile($pathname)
    {
        $zip = new ZipArchive;
        if ($zip->open($pathname) === TRUE)
        {
            $zip->extractTo('data');
            $zip->close();

            echo "Extracted $pathname<br>";
        }
        else
        {
            echo "Failed to extract $pathname<br>";
        }
    }


    // Credentials and DB name are coded in db_credentials.php
    $db = new db_credentials();

    $reports_table = 'reports';

    echo 'db_exists = '.(db_exists($db) ? 'YES' : 'NO').'<br>';
    echo 'table_exists = '.(table_exists($db, $reports_table) ? 'YES' : 'NO').'<br>';

    if (db_exists($db) && table_exists($db, $reports_table) )
    {
        echo('Dropping table reports...<br>');
        drop_table($db, $reports_table);
    }

    echo 'table_exists = '.(table_exists($db, $reports_table) ? 'YES' : 'NO').'<br>';

    // If the database doesn't exist, create it and add some dummy data
    if (!db_exists($db) )
    {
        echo('Creating database...<br>');
        create_db($db);
    }

    if (!table_exists($db, $reports_table) )
    {
        echo('Adding tables...<br>');
        add_tables($db);

        echo('Adding dummy data...<br>');

        // Prescan - look for zip files and extract them
        $filenames = scandir('data');

        foreach ($filenames as $filename)
        {
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);

            if (0 == strcasecmp('zip', $fileext) )
            {
                extract_zipfile('data/'.$filename);
            }
        }

        // Now look for csv files and import them
        $filenames = scandir('data');

        echo count($filenames).' files found in data folder<br>';

        foreach ($filenames as $filename)
        {
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);

            if (0 == strcasecmp('csv', $fileext) )
            {
                echo("Importing data from $filename...<br>");

                add_data_from_file($db, 'data/'.$filename);
            }
            else
            {
                echo("Skipping $filename<br>");
            }
        }
    }
?>

<p>Database rebuilt.</p>
