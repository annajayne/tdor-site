<?php
    /**
     * Support functions and classes for administrative functions.
     *
     */

    require_once('util/string_utils.php');                      // For get_random_hex_string()
    require_once('util/datetime_utils.php');                    // For date_str_to_iso()
    require_once('models/reports.php');
    require_once('models/report_utils.php');
    require_once('models/report_events.php');
    require_once('models/items_change_details.php');            // For DatabaseItemsChangeDetails
    require_once('util/geocode.php');



    /**
     * Extract the specified zipfile to the given folder.
     *
     * @param string $pathname              The pathname of the zipfile.
     * @param string $dest_folder           The path of the destination folder.
     *
     */
    function extract_zipfile($pathname, $dest_folder)
    {
        $files_in_archive = [];

        $archive = new ZipArchive;
        if ($archive->open($pathname) === TRUE)
        {
            $archive->extractTo($dest_folder);

            for ($i = 0; $i < $archive->numFiles; ++$i)
            {
                $stat = $archive->statIndex($i);

                $filename = $stat['name'];

                $files_in_archive[] = $filename;
            }

            $archive->close();

            echo "Extracted $pathname<br>";
        }
        else
        {
            echo "Failed to extract $pathname<br>";
        }
        return $files_in_archive;
    }


    class ReportsImporter
    {
        /**
         * Add reports  to the database corresponding to the specified CSV items
         *
         * @param array $csv_items              An array of CSV items
         * @param Reports $reports_table        The existing "reports" table.
         * @param Reports $temp_reports_table   The temporary "reports" table (optional - if not supplied, reports_table will be updated directly)
        * @return DatabaseItemsChangeDetails     Details of the changes made as a result.
         */
        public static function add_csv_items($csv_items, $reports_table, $temp_reports_table)
        {
            $results                = new DatabaseItemsChangeDetails;

            $today                  = date("Y-m-d");

            $root                   = get_root_path();
            $thumbnails_folder      = "$root/data/thumbnails";

            $db_exists              = db_exists($reports_table->db);
            $reports_table_exists   = table_exists($reports_table->db, $reports_table->table_name);
            $add_to_temp_table      = ($temp_reports_table != null);

            $report_changes         = [];

            foreach ($csv_items as $csv_item)
            {
                $has_uid = !empty($csv_item->uid);

                if (!$has_uid)
                {
                    do
                    {
                        // Generate a new uid and check for clashes with existing entries
                        $uid                    = get_random_hex_string();

                        $id1                    = ($db_exists && $reports_table_exists) ? $reports_table->find_id_from_uid($uid) : 0;       // Check for clashes with the existing table...
                        $id2                    = ($db_exists && $add_to_temp_table) ? $temp_reports_table->find_id_from_uid($uid) : 0;     // ...and the new table

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
                $report_changed                 = false;

                $existing_id                    = 0;
                $existing_report                = null;

                // Compare entries between the reports table ($reports_table) and temp reports table ($temp_reports_table)
                // For any entries which are different, set the added or updated fields accordingly
                if ($has_uid && $reports_table_exists)
                {
                    $existing_id = $reports_table->find_id_from_uid($csv_item->uid);

                    if ($existing_id > 0)
                    {
                        $existing_report = $reports_table->find($existing_id);

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
                        if (self::report_contents_match($csv_item, $existing_report) )
                        {
                            echo "&nbsp;&nbsp;Unchanged record $csv_item->date / $csv_item->name / $csv_item->location ($csv_item->country)<br>";
                        }
                        else
                        {
                            echo "&nbsp;&nbsp;<b>Updating record $csv_item->date / $csv_item->name / $csv_item->location ($csv_item->country)</b><br>";

                            $csv_item->date_updated = $today;

                            $report_changed         = true;
                        }
                    }
                    else
                    {
                        $new_report = true;
                    }
                }

                if ($new_report)
                {
                    $has_permalink_msg = '';

                    if (!$has_uid)
                    {
                        $has_permalink_msg = ' [<i>Warning: no permalink defined. This could cause duplicate entries</i>]';
                    }

                    echo "&nbsp;&nbsp;<b>Adding record $csv_item->date / $csv_item->name / $csv_item->location ($csv_item->country)</b> $has_permalink_msg<br>";
                }

                $report = self::get_report_from_csv_item($csv_item, $existing_id);

                if ($add_to_temp_table)
                {
                    $temp_reports_table->add($report);
                }
                else
                {
                    if ($new_report)
                    {
                        $reports_table->add($report);
                    }
                    else if ($report_changed)
                    {
                        $reports_table->update($report);
                    }
                }

                if ($new_report)
                {
                    $results->items_added[] = $report;
                }

                if ($existing_report && $report_changed)
                {
                    $results->items_updated[] = $report;
                    $results->changed_properties[$report->uid] = Reports::get_changed_properties($report, $existing_report);
                }

                // Generate the QR code image file at the end if it doesn't exist
                if ($has_uid)
                {
                    $pathname = get_root_path().'/'.get_qrcode_filename($csv_item);

                    if (!file_exists($pathname) )
                    {
                        $results->qrcodes_to_generate[] = $csv_item;
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
            return $results;
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
        private static function report_contents_match($report1, $report2)
        {
            if ( ($report1->name == $report2->name) &&
                 ($report1->age == $report2->age) &&
                 ($report1->photo_filename == $report2->photo_filename) &&
                 ($report1->photo_source == $report2->photo_source) &&
                 (date_str_to_iso($report1->birthdate) == date_str_to_iso($report2->birthdate) ) &&
                 (date_str_to_iso($report1->date) == date_str_to_iso($report2->date) ) &&
                 ($report1->tdor_list_ref == $report2->tdor_list_ref) &&
                 ($report1->location == $report2->location) &&
                 ($report1->country == $report2->country) &&
                 ($report1->cause == $report2->cause) &&
                 ($report1->description == $report2->description) &&
                 ($report1->tweet == $report2->tweet) &&
                 ($report1->draft == $report2->draft) &&
                 ($report1->deleted == $report2->deleted) )
            {
                return true;
            }
            return false;
        }


        /**
         * Get a report object from the corresponding CSV item.
         *
         * @param tdor_csv_item $csv_item       The CSV item.
         * @param int $id                       The id of the report.
         * @return Report                       The corresponding report.
     */
        function get_report_from_csv_item($csv_item, $id)
        {
            $report = new Report();

            $report->id                 = $id;

            $report->uid                = $csv_item->uid;
            $report->draft              = $csv_item->draft;
            $report->name               = $csv_item->name;
            $report->age                = $csv_item->age;
            $report->photo_filename     = $csv_item->photo_filename;
            $report->photo_source       = $csv_item->photo_source;
            $report->birthdate          = $csv_item->birthdate;
            $report->date               = $csv_item->date;
            $report->tdor_list_ref      = $csv_item->tdor_list_ref;
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


    }

?>