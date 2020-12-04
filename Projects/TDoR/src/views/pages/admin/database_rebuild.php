<?php
    /**
     * Administrative command to rebuild the database.
     *
     */

    require_once('models/reports.php');
    require_once('models/report_utils.php');
    require_once('models/report_events.php');
    require_once('geocode.php');


    /**
     * Class to record the results of a database rebuild/import.
     *
     */
    class DatabaseRebuildResults
    {
        /** @var array                  Reports added */
        public  $reports_added;

        /** @var array                  Reports updated */
        public  $reports_updated;

        /** @var array                  Reports deleted */
        public  $reports_deleted;

        /** @var array                  QR codes to generate */
        public  $qrcodes_to_generate;


        public function __construct()
        {
            $this->reports_added        = array();
            $this->reports_updated      = array();
            $this->reports_deleted      = array();
            $this->qrcodes_to_generate  = array();
        }


        public function add($results)
        {
            $this->reports_added          = array_merge($this->reports_added,       $results->reports_added);
            $this->reports_updated        = array_merge($this->reports_updated,     $results->reports_updated);
            $this->reports_deleted        = array_merge($this->reports_deleted,     $results->reports_deleted);
            $this->qrcodes_to_generate    = array_merge($this->qrcodes_to_generate, $results->qrcodes_to_generate);
        }
    }


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
             (date_str_to_iso($report1->birthdate) == date_str_to_iso($report2->birthdate) ) &&
             (date_str_to_iso($report1->date) == date_str_to_iso($report2->date) ) &&
             ($report1->source_ref == $report2->source_ref) &&
             ($report1->location == $report2->location) &&
             ($report1->country == $report2->country) &&
             ($report1->cause == $report2->cause) &&
             ($report1->description == $report2->description) &&
             ($report1->tweet == $report2->tweet) )
        {
            return true;
        }
        return false;
    }


    /**
     * Get a report object from the corresponding CSV item.
     *
     * @param tdor_csv_item $csv_item       The CSV item.
     * @return Report                       The corresponding report.
 */
    function get_report_from_csv_item($csv_item)
    {
        $report = new Report();

        $report->uid                = $csv_item->uid;
        $report->draft              = $csv_item->draft;
        $report->name               = $csv_item->name;
        $report->age                = $csv_item->age;
        $report->photo_filename     = $csv_item->photo_filename;
        $report->photo_source       = $csv_item->photo_source;
        $report->birthdate          = $csv_item->birthdate;
        $report->date               = $csv_item->date;
        $report->source_ref         = $csv_item->source_ref;
        $report->location           = $csv_item->location;
        $report->country            = $csv_item->country;
        $report->country_code       = get_country_code($csv_item->country);
        $report->latitude           = $csv_item->latitude;
        $report->longitude          = $csv_item->longitude;
        $report->category           = $csv_item->category;
        $report->cause              = $csv_item->cause;
        $report->description        = $csv_item->description;
        $report->tweet              = $csv_item->tweet;
        $report->permalink          = $csv_item->permalink;
        $report->date_created       = $csv_item->date_created;
        $report->date_updated       = $csv_item->date_updated;

        if (!empty($csv_item->status) && (strcasecmp('draft', $csv_item->status) == 0) )
        {
            $report->draft          = true;
        }

        if (empty($csv_item->category) )
        {
            $report->category       = Report::get_category($report);
        }
        return $report;
    }


    /**
     * Add reports  to the database corresponding to the items in the specified CSV file.
     *
     * @param db_credentials $db            The database credentials (TODO: REMOVE AS NOT NEEDED).
     * @param Reports $reports_table        The existing "reports" table.
     * @param Reports $temp_reports_table   The temporary "reports" table.
     * @param string $pathname              The pathname of the CSV file.
     * @return DatabaseRebuildResults       Details of the results of the operation.
     */
    function add_data_from_file($db, $reports_table, $temp_reports_table, $pathname)
    {
        $results                    = new DatabaseRebuildResults;

        $today                      = date("Y-m-d");

        $root                       = get_root_path();
        $thumbnails_folder          = "$root/data/thumbnails";

        if (file_exists($pathname) )
        {
            log_text("Reading $pathname");

            $db_exists              = db_exists($db);
            $reports_table_exists   = table_exists($db, $reports_table->table_name);

            $csv_items = read_csv_file($pathname);

            foreach ($csv_items as $csv_item)
            {
                echo "&nbsp;&nbsp;Importing record $csv_item->date / $csv_item->name / $csv_item->location ($csv_item->country)<br>";

                $has_uid = !empty($csv_item->uid);

                if (!$has_uid)
                {
                    do
                    {
                        // Generate a new uid and check for clashes with existing entries
                        $uid                    = get_random_hex_string();

                        $id1                    = ($db_exists && $reports_table_exists) ? $reports_table->find_id_from_uid($uid) : 0;       // Check for clashes with the existing table
                        $id2                    = $db_exists ? $temp_reports_table->find_id_from_uid($uid) : 0;             // ...and the new table

                        if ( ($id1 == 0) && ($id2 == 0) )
                        {
                            $csv_item->uid      = $uid;
                        }
                    } while (empty($csv_item->uid) );
                }

                $csv_item->permalink            = get_permalink($csv_item);
                $csv_item->date_created         = $today;
                $csv_item->date_updated         = $today;

                $new_report                     = !$has_uid;
                $existing_report                = false;
                $report_changed                 = false;

                // Compare entries between the reports table ($reports_table) and temp reports table ($temp_reports_table)
                // For any entries which are different, set the added or updated fields accordingly
                if ($has_uid && $reports_table_exists)
                {
                    $existing_id = $reports_table->find_id_from_uid($csv_item->uid);

                    if ($existing_id > 0)
                    {
                        $existing_report    = $reports_table->find($existing_id);

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

                            $existing_report        = true;
                            $report_changed         = true;
                        }
                    }
                    else
                    {
                        $new_report = true;
                    }
                }

                $report = get_report_from_csv_item($csv_item);

                $temp_reports_table->add($report);

                if ($new_report)
                {
                    $results->reports_added[] = $report;
                }

                if ($existing_report && $report_changed)
                {
                    //TODO: diff the two reports. Only add if they are different.
                    $results->reports_updated[] = $report;
                }

                // Generate the QR code image file at the end if it doesn't exist
                if ($has_uid)
                {
                    $pathname = get_root_path().'/'.get_qrcode_filename($csv_item);

                    if (!file_exists($pathname) )
                    {
                        $results->qrcodes_todo[] = $csv_item;
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
        return $results;
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

        echo '<b>Rebuilding database</b> [<a href="#change_details">Summary of changes</a>]<br><br>';

        $results                    = new DatabaseRebuildResults;

        $temp_reports_table_name    = 'reports_temp';

        // Credentials and DB name are coded in db_credentials.php
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

                            $results_for_file = add_data_from_file($db, $reports_table, $temp_reports_table, 'data/'.$filename);

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

            echo '<br><br><a href="#top">[Back to top</a>]';

            ob_end_flush();

            $caption .= ' by '.get_logged_in_username();

            $html = ReportEvents::reports_changed($caption, $results->reports_added, $results->reports_updated, $results->reports_deleted);

            if (!empty($results->qrcodes_todo) )
            {
                foreach ($results->qrcodes_todo as $report)
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

                echo '<br><br>[<a href="#top">Back to top</a>]';
            }
        }
    }


?>
