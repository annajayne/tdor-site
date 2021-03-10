<?php
    /**
     * Administrative command to rebuild the database.
     *
     */
    require_once('views/pages/admin/admin_utils.php');




    /**
     * Rebuild the database.
     *
     */
    function rebuild_database()
    {
        ob_start();

        echo '<b>Rebuilding database</b> [<a href="#change_details">Summary of changes</a>]<br><br>';

        $results                    = new DatabaseRebuildResults;

        $temp_reports_table_name    = 'reports_temp';

        $db                         = new db_credentials();

        $reports_table              = new Reports($db);
        $temp_reports_table         = new Reports($db, $temp_reports_table_name);

        $users_table                = new Users($db);

        // If the database doesn't exist, attempt to create it and add some dummy data
        $db_exists                  = db_exists($db);
        $reports_table_exists       = $db_exists && table_exists($db, $reports_table->table_name);
        $temp_reports_table_exists  = $db_exists && table_exists($db, $temp_reports_table->table_name);

        echo 'db_exists = '.($db_exists ? 'YES' : 'NO').'<br>';

        echo "$reports_table->table_name table exists = ".($reports_table_exists ? 'YES' : 'NO').'<br>';
        echo "$temp_reports_table->table_name table exists = ".($temp_reports_table_exists ? 'YES' : 'NO').'<br>';

        if ($db_exists && $temp_reports_table_exists)
        {
            echo("Dropping $temp_reports_table->table_name table...<br>");
            drop_table($db, $temp_reports_table->table_name);
        }

        // If the database doesn't exist, attempt to create it and add some dummy data
        if (!$db_exists)
        {
            echo('Creating database...<br>');
            create_db($db);

            $db_exists = db_exists($db);
        }

        if ($db_exists)
        {
            if (!table_exists($db, $users_table->table_name) )
            {
                echo("Adding $users_table->table_name table...<br>");
                $users_table->create_table();
            }

            if (!table_exists($db, $temp_reports_table->table_name) )
            {
                echo("Adding $temp_reports_table->table_name table...<br>");
                $temp_reports_table->create_table();


                echo('Importing data...<br>');

                // Prescan - look for zip files and extract them
                $data_folder = 'data';

                if (file_exists($data_folder) )
                {
                    $thumbnails_folder_path = get_root_path()."/$data_folder/thumbnails";

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

                            $csv_items = read_csv_file("data/$filename");

                            $results_for_file = ReportsImporter::add_csv_items($csv_items, $reports_table, $temp_reports_table);

                            $results->add($results_for_file);
                        }
                        else
                        {
                            echo("Skipping $filename<br>");
                        }
                    }
                }
           }

            // Rename the 'reports' table as 'reports_backup_<date>' and rename 'reports_temp' as 'reports'
            $timenow                    = new DateTime('now');
            $timestamp                  = $timenow->format('Y_m_d\TH_i_s');

            $reports_backup_table_name  = 'reports_backup_'.$timestamp;

            if (table_exists($db, $reports_backup_table_name) )
            {
                // It shouldn't exist, but lets be careful.
                drop_table($db, $reports_backup_table_name);
            }

            if ($reports_table_exists)
            {
                $results->reports_deleted = $reports_table->get_all_missing_from($temp_reports_table->table_name);

                rename_table($db, $reports_table->table_name, $reports_backup_table_name);
            }

            rename_table($db, $temp_reports_table->table_name, $reports_table->table_name);

            $caption = raw_get_host().' - database rebuilt';

            echo "$caption<br>";

            echo '<br><br><a href="#top">[Back to top</a>]<br>';

            ob_end_flush();

            $caption .= ' by '.get_logged_in_username();

            $html = ReportEvents::reports_changed($caption, $results->reports_added, $results->reports_updated, $results->reports_deleted);

            if (!empty($results->qrcodes_to_generate) )
            {
                foreach ($results->qrcodes_to_generate as $report)
                {
                    // Generate QR code image file
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;Creating qrcode for '.get_host().get_permalink($report).'<br>';

                    create_qrcode_for_report($report, false);
                }

                echo 'QR codes generated<br>';
            }

            if (!empty($html) )
            {
                echo '<br><hr><br>'.$html;

                echo '<br><br>[<a href="#top">Back to top</a>]<br>';
            }
        }
    }


?>
