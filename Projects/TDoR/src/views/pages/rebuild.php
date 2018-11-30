<?php
    /**
     * Administrative commands to rebuild the database etc.
     *
     */


    /**
     * Determine whether the contents of the two reports match.
     *
     * Note that the id and uid are *not* matched in this context.
     *
     * @param Report $report1               The first report.
     * @param Report $report2               The second report.
     * @return boolean                      true if the two reports match; false otherwise.
     */
    function report_contents_match($report1, $report2)
    {
        if ( ($report1->name == $report2->name) &&
             ($report1->age == $report2->age) &&
             ($report1->photo_filename == $report2->photo_filename) &&
             ($report1->photo_source == $report2->photo_source) &&
             (date_str_to_iso($report1->date) == date_str_to_iso($report2->date) ) &&
             ($report1->source_ref == $report2->source_ref) &&
             ($report1->location == $report2->location) &&
             ($report1->country == $report2->country) &&
             ($report1->cause == $report2->cause) &&
             ($report1->description == $report2->description) )
        {
            return true;
        }
        return false;
    }


    /**
     * Add a report corresponding to the given CSV item to the database.
     *
     * @param db_credentials $db            The database credentials (TODO: REMOVE AS NOT NEEDED).
     * @param string $temp_reports_table    The name of the "reports" table.
     * @param tdor_csv_item $csv_item       The CSV item to add,
     */
    function add_data($db, $temp_reports_table, $csv_item)
    {
        require_once('models/report.php');

        $report = new Report();

        $report->uid                = $csv_item->uid;
        $report->name               = $csv_item->name;
        $report->age                = $csv_item->age;
        $report->photo_filename     = $csv_item->photo_filename;
        $report->photo_source       = $csv_item->photo_source;
        $report->date               = $csv_item->date;
        $report->source_ref         = $csv_item->source_ref;
        $report->location           = $csv_item->location;
        $report->country            = $csv_item->country;
        $report->latitude           = $csv_item->latitude;
        $report->longitude          = $csv_item->longitude;
        $report->cause              = $csv_item->cause;
        $report->description        = $csv_item->description;
        $report->permalink          = $csv_item->permalink;
        $report->date_created       = $csv_item->date_created;
        $report->date_updated       = $csv_item->date_updated;

        Reports::add($report, $temp_reports_table);
    }


    /**
     * Add reports  to the database corresponding to the items in the specified CSV file.
     *
     * @param db_credentials $db            The database credentials (TODO: REMOVE AS NOT NEEDED).
     * @param string $temp_reports_table    The name of the "reports" table.
     * @param string $pathname              The pathname of the CSV file.
     * @return array                        An array of items idenfying CSV files to generate.
     */
    function add_data_from_file($db, $temp_reports_table, $pathname)
    {
        require_once('models/report.php');

        $reports_table      = 'reports';

        $qrcodes_todo       = array();
        $today              = date("Y-m-d");

        $root               = get_root_path();
        $thumbnails_folder  = "$root/data/thumbnails";

        if (file_exists($pathname) )
        {
            log_text("Reading $pathname");

            $db_exists              = db_exists($db);
            $reports_table_exists   = table_exists($db, $reports_table);

            $csv_items = read_csv_file($pathname);

            foreach ($csv_items as $csv_item)
            {
                echo "&nbsp;&nbsp;Adding record $csv_item->date / $csv_item->name / $csv_item->location ($csv_item->country)<br>";

                $has_uid = !empty($csv_item->uid);

                if (!$has_uid)
                {
                    do
                    {
                        // Generate a new uid and check for clashes with existing entries
                        $uid                    = get_random_hex_string();

                        $id1                    = ($db_exists && $reports_table_exists) ? Reports::find_id_from_uid($uid) : 0;  // Check for clashes with the existing table
                        $id2                    = $db_exists ? Reports::find_id_from_uid($uid, $temp_reports_table) : 0;             // ...and the new table

                        if ( ($id1 == 0) && ($id2 == 0) )
                        {
                            $csv_item->uid      = $uid;
                        }
                    } while (empty($csv_item->uid) );
                }

                $csv_item->permalink            = get_permalink($csv_item);
                $csv_item->date_created         = $today;
                $csv_item->date_updated         = $today;

                // Compare entries between the reports table and reports_temp ($temp_reports_table)
                // For any entries which are different, set the added or updated fields accordingly
                if ($has_uid && $reports_table_exists)
                {
                    $existing_id = Reports::find_id_from_uid($csv_item->uid);

                    if ($existing_id > 0)
                    {
                        $existing_report = Reports::find($existing_id);

                        if (!empty($existing_report->date_created) )
                        {
                            $csv_item->date_created = $existing_report->date_created;
                        }

                        if (!empty($existing_report->date_updated) )
                        {
                            $csv_item->date_updated = $existing_report->date_updated;
                        }

                        if ( ($csv_item->location == $existing_report->location) && ($csv_item->country == $existing_report->country) )
                        {
                            // Preserve geographic coordinates if the location hasn't changed
                            if (empty($csv_item->latitude) && !empty($existing_report->latitude) )
                            {
                                $csv_item->latitude = $existing_report->latitude;
                            }
                            if (empty($csv_item->longitude) && !empty($existing_report->longitude) )
                            {
                                $csv_item->longitude = $existing_report->longitude;
                            }
                        }

                        // If the entries are different, update the "last_updated" field
                        if (!report_contents_match($csv_item, $existing_report) )
                        {
                            $csv_item->date_updated = $today;
                        }
                    }
                }

                add_data($db, $temp_reports_table, $csv_item);

                // Generate the QR code image file at the end if it doesn't exist
                if ($has_uid)
                {
                    $pathname = get_root_path().'/'.get_qrcode_filename($csv_item);

                    if (!file_exists($pathname) )
                    {
                        $qrcodes_todo[] = $csv_item;
                    }
                }

                if (!empty($csv_item->photo_filename) )
                {
                    $thumbnail_pathname = "$thumbnails_folder/$csv_item->photo_filename";

                    if (!file_exists($thumbnail_pathname) )
                    {
                        // TODO: add to a list instead, and do them at the end like the QR codes.
                        create_photo_thumbnail($csv_item->photo_filename);
                    }
                }
            }
        }
        return $qrcodes_todo;
    }


    /**
     * Extract the specified zipfile.
     *
     * @param string $pathname              The pathname of the zipfile.
     */
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


    /**
     * Rebuild the database.
     *
     */
    function rebuild_database()
    {
        ob_start();

        $qrcodes_todo               = array();

        $reports_table              = 'reports';
        $temp_reports_table         = 'reports_temp';
        $users_table                = 'users';

        // Credentials and DB name are coded in db_credentials.php
        $db                         = new db_credentials();

        // If the database doesn't exist, attempt to create it and add some dummy data
        $db_exists                  = db_exists($db);
        $reports_table_exists       = $db_exists && table_exists($db, $reports_table);
        $temp_reports_table_exists  = $db_exists && table_exists($db, $temp_reports_table);

        echo 'db_exists = '.($db_exists ? 'YES' : 'NO').'<br>';

        echo "$reports_table table exists = ".($reports_table_exists ? 'YES' : 'NO').'<br>';
        echo "$temp_reports_table table exists = ".($temp_reports_table_exists ? 'YES' : 'NO').'<br>';

        if ($db_exists && $temp_reports_table_exists)
        {
            echo("Dropping $temp_reports_table table...<br>");
            drop_table($db, $temp_reports_table);
        }

        // If the database doesn't exist, attempt to create it and add some dummy data
        if (!$db_exists)
        {
            echo('Creating database...<br>');
            create_db($db);

            $db_exists      = db_exists($db);
        }

        if ($db_exists)
        {
            if (!table_exists($db, $users_table) )
            {
                echo("Adding $users_table table...<br>");
                add_users_table($db);
            }

            if (!table_exists($db, $temp_reports_table) )
            {
                echo("Adding $temp_reports_table table...<br>");
                add_reports_table($db, $temp_reports_table);

                echo('Adding data...<br>');

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

                            $qrcodes_todo_for_file = add_data_from_file($db, $temp_reports_table, 'data/'.$filename);

                            if (!empty($qrcodes_todo_for_file) )
                            {
                                foreach ($qrcodes_todo_for_file as $csv_item)
                                {
                                    $qrcodes_todo[] = $csv_item;
                                }
                            }
                        }
                        else
                        {
                            echo("Skipping $filename<br>");
                        }
                    }
                }
            }


            // Delete the reports table and rename reports_temp as reports
            if ($reports_table_exists)
            {
                drop_table($db, $reports_table);
            }

            rename_table($db, $temp_reports_table, $reports_table);

            echo 'Database rebuilt<br>';

            echo ob_get_contents();
            ob_end_flush();

            if (!empty($qrcodes_todo) )
            {
                foreach ($qrcodes_todo as $csv_item)
                {
                    // Generate QR code image file
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;Creating qrcode for '.get_host().get_permalink($csv_item).'<br>';

                    create_qrcode_for_report($csv_item, false);
                }

                echo 'QR codes generated<br>';
            }
        }
    }


    /**
     * Rebuild thumbnail image files.
     *
     */
    function rebuild_thumbnails()
    {
        require_once('models/report.php');

        $reports = Reports::get_all();

        foreach ($reports as $report)
        {
            if (!empty($report->photo_filename) )
            {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;Creating thumbnail for '.get_host().get_permalink($report).'<br>';

                create_photo_thumbnail($report->photo_filename);
            }
        }

        echo 'Thumbnails generated<br>';
    }


    /**
     * Rebuild QR code image files.
     *
     */
    function rebuild_qrcodes()
    {
        require_once('models/report.php');

        $reports = Reports::get_all();

        foreach ($reports as $report)
        {
            // Generate QR code image file if it doesn't exist
            $pathname = get_root_path().'/'.get_qrcode_filename($report);

            if (!file_exists($pathname) )
            {
                // Generate QR code image file
                echo '&nbsp;&nbsp;&nbsp;&nbsp;Creating qrcode for '.get_host().get_permalink($report).'<br>';

                create_qrcode_for_report($report, false);
            }
        }

        echo 'QR codes generated<br>';
    }

    function geocode_locations_impl($locations)
    {
        $geocoder_batch_limit   = 100;

        $chunks = array_chunk($locations, $geocoder_batch_limit, TRUE);

        $geocoded_places = array();

        foreach ($chunks as $chunk)
        {
            $batch_geocoded_places = geocode($chunk);

            foreach ($batch_geocoded_places as $geocoded_place)
            {
                $key = $geocoded_place['location'].'|'.$geocoded_place['country'];

                $geocoded_places[$key] = $geocoded_place;
            }
        }
        return $geocoded_places;
    }


    function get_geocode_location_key($location, $country)
    {
        return "$location|$country";
    }


    function geocode_locations()
    {
        require_once('models/report.php');
        require_once('geocode.php');

        $reports = Reports::get_all();

        $reports_to_geocode = array();

        $locations = array();

        foreach ($reports as $report)
        {
            if (empty($report->latitude) || empty($report->longitude) )
            {
                $reports_to_geocode[] = $report;

                $key = get_geocode_location_key($report->location, $report->country);

                if (empty($locations[$key]) )
                {
                    $place = array();

                    $place['location']  = $report->location;
                    $place['country']   = $report->country;

                    $locations[$key]    = $place;
                }
            }
        }

        if (!empty($locations) )
        {
            $geocoded_places = geocode_locations_impl($locations);

            if (!empty($reports_to_geocode) )
            {
                foreach ($reports_to_geocode as $report)
                {
                    $key        = get_geocode_location_key($report->location, $report->country);

                    $permalink  = get_permalink($report);
                    $date       = get_display_date($report);
                    $place      = !empty($report->location) ? "$report->location, $report->country" : $report->country;

                    if (!empty($geocoded_places[$key]['lat']) )
                    {
                        $report->latitude   = $geocoded_places[$key]['lat'];
                        $report->longitude  = $geocoded_places[$key]['lon'];

                        echo "Geocoded <a href='$permalink'><b>$report->name</b></a> ($date / $place)<br>";

                        Reports::update($report);
                    }
                    else
                    {
                        echo "WARNING: Unable to geocode <a href='$permalink'><b>$report->name</b></a> ($date / $place)<br>";
                    }
                }
            }
        }
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

        case 'geocode':
            geocode_locations();
            break;

        default:
            rebuild_database();
            break;
    }

?>
