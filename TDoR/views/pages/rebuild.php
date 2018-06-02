<?php
    function add_data($db, $csv_item)
    {
        $conn = new PDO("mysql:host=$db->servername;dbname=$db->dbname", $db->username, $db->password, $db->pdo_options);

        $comma = ', ';

        $sql = 'INSERT INTO reports (uid, name, age, photo_filename, photo_source, date, tgeu_ref, location, country, cause, description, permalink) VALUES ('.
            $conn->quote($csv_item->uid).$comma.
            $conn->quote($csv_item->name).$comma.
            $conn->quote($csv_item->age).$comma.
            $conn->quote($csv_item->photo_filename).$comma.
            $conn->quote($csv_item->photo_source).$comma.
            $conn->quote(date_str_to_iso($csv_item->date) ).$comma.
            $conn->quote($csv_item->tgeu_ref).$comma.
            $conn->quote($csv_item->location).$comma.
            $conn->quote($csv_item->country).$comma.
            $conn->quote($csv_item->cause).$comma.
            $conn->quote($csv_item->description).$comma.
            $conn->quote($csv_item->permalink).')';

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
            log_text("Record for $csv_item->name added successfully");
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

            $csv_items = read_csv_file($pathname);

            foreach ($csv_items as $csv_item)
            {
                echo "&nbsp;&nbsp;Adding record $csv_item->date / $csv_item->name / $csv_item->location ($csv_item->country)<br>";

                if (empty($csv_item->uid) )
                {
                    // TODO: check for clashes with existing entries
                    $csv_item->uid = get_random_hex_string();
                }

                $csv_item->permalink = get_permalink($csv_item);

                add_data($db, $csv_item);
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


    function create_homepage_slider_images()
    {
        require_once('models/report.php');

        $root = $_SERVER['DOCUMENT_ROOT'];

        $recent_reports = Reports::get_most_recent(HOMEPAGE_SLIDER_ITEMS);

        if (!empty($recent_reports) )
        {
            $default_image_pathname = get_photo_pathname('');

            $folder = "$root/data/slider/";

            if (!file_exists($folder) )
            {
                $ok =  mkdir($folder, 0644);
            }

            foreach ($recent_reports as $report)
            {
                if ($report->photo_filename !== '')
                {
                    $slider_image_pathname = "$folder/$report->photo_filename";

                    if (create_overlay_image($slider_image_pathname, get_photo_pathname($report->photo_filename), $default_image_pathname) )
                    {
                        echo "  Slider image $slider_image_pathname created<br>";
                    }
                    else
                    {
                        echo "  ERROR: Slider image $slider_image_pathname NOT created<br>";
                    }
                }
            }
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
        $data_folder = 'data';

        if (file_exists($data_folder) )
        {
            $filenames = scandir($data_folder);

            foreach ($filenames as $filename)
            {
                $fileext = pathinfo($filename, PATHINFO_EXTENSION);

                if (0 == strcasecmp('zip', $fileext) )
                {
                    extract_zipfile('data/'.$filename);
                }
            }

            // Now look for csv files and import them
            $filenames = scandir($data_folder);

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

            create_homepage_slider_images();
        }
    }
?>

<p>Database rebuilt.</p>
