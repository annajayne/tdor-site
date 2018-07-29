<?php
    function add_data($db, $csv_item)
    {
        require_once('models/report.php');

        $report = new Report();

        $report->uid                = $csv_item->uid;
        $report->name               = $csv_item->name;
        $report->age                = $csv_item->age;
        $report->photo_filename     = $csv_item->photo_filename;
        $report->photo_source       = $csv_item->photo_source;
        $report->date               = $csv_item->date;
        $report->tgeu_ref           = $csv_item->tgeu_ref;
        $report->location           = $csv_item->location;
        $report->country            = $csv_item->country;
        $report->cause              = $csv_item->cause;
        $report->description        = $csv_item->description;
        $report->permalink          = $csv_item->permalink;

        Reports::add($report);
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

                // Generate QR code image file if it doesn't exist
                create_qrcode_for_report($csv_item, false);

                if (!empty($csv_item->photo_filename) )
                {
                    create_photo_thumbnail($csv_item->photo_filename);
                }
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


    function rebuild_database()
    {
        ob_start();

        $reports_table  = 'reports';
        $users_table    = 'users';

        // Credentials and DB name are coded in db_credentials.php
        $db = new db_credentials();

        // If the database doesn't exist, attempt to create it and add some dummy data
        echo 'db_exists = '.(db_exists($db) ? 'YES' : 'NO').'<br>';
        echo 'table_exists = '.(table_exists($db, $reports_table) ? 'YES' : 'NO').'<br>';

        if (db_exists($db) && table_exists($db, $reports_table) )
        {
            echo('Dropping table reports...<br>');
            drop_table($db, $reports_table);
        }

        echo 'table_exists = '.(table_exists($db, $reports_table) ? 'YES' : 'NO').'<br>';

        // If the database doesn't exist, attempt to create it and add some dummy data
        if (!db_exists($db) )
        {
            echo('Creating database...<br>');
            create_db($db);
        }

        if (!table_exists($db, $users_table) )
        {
            echo("Adding $users_table table...<br>");
            add_users_table($db);
        }

        if (!table_exists($db, $reports_table) )
        {
            echo("Adding $reports_table table...<br>");
            add_reports_table($db);

            echo('Adding dummy data...<br>');

            // Prescan - look for zip files and extract them
            $data_folder = 'data';

            if (file_exists($data_folder) )
            {
                $thumbnails_folder_path = $_SERVER['DOCUMENT_ROOT']."/$data_folder/thumbnails";

                if (!file_exists($thumbnails_folder_path) )
                {
                    mkdir($thumbnails_folder_path);
                }

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
            }
        }

        echo 'Database rebuilt<br>';

        echo ob_get_contents();
        ob_end_flush();
    }


    function rebuild_thumbnails()
    {
        require_once('models/report.php');

        $reports = Reports::get_all();

        foreach ($reports as $report)
        {
            if (!empty($report->photo_filename) )
            {
                create_photo_thumbnail($report->photo_filename);
            }
        }

        echo 'Thumbnails generated<br>';
    }


    function rebuild_qrcodes()
    {
        require_once('models/report.php');

        $reports = Reports::get_all();

        foreach ($reports as $report)
        {
            // Generate QR code image file if it doesn't exist
            create_qrcode_for_report($report, false);
        }

        echo 'QR codes generated<br>';
    }


    $target = $_GET['target'];

    switch ($target)
    {
        case 'thumbnails':
            rebuild_thumbnails();
            break;

        case 'qrcodes':
            rebuild_qrcodes();
            break;

        default:
            rebuild_database();
            break;
    }

?>
