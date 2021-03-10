<?php
    /**
     * Administrative command to import one or more archive report zipfiles.
     *
     */

    require_once('views/pages/admin/admin_utils.php');



    /**
     * Implementation of the "Import Reports" admin page.
     *
     */
    function import_reports()
    {
        echo '<script src="/js/import_reports.js"></script>';

        echo '<h2>Import Reports</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';

        // Browse for zipfile
        echo     '<div class="grid_12">';
        echo       '<label for="zipfiles">Zipfiles:<br></label>';
        echo       '<input type="file" name="zipfiles[]" id="zipfileUpload" accept="application/zip" multiple />';
        echo       '</br/>';
        echo       '</br/>';
        echo       '<div id="zipfile-contents-placeholder"></div>';
        echo       '<input type="submit" name="submit" id="submit" value="Import" class="button-green" style="display:none;" />&nbsp;&nbsp;';
        echo     '</div>';

        echo   '</div>';
        echo '</form>';

        echo   '<div class="grid_12" id="output">';

        if (isset($_POST['submit']) )
        {
            if (isset($_FILES["zipfiles"]) )
            {
                $target_dir             = "data";

                $filenames = array();

                foreach ($_FILES["zipfiles"]["error"] as $key => $error)
                {
                    $target_filename        = basename($_FILES["zipfiles"]["name"][$key]);

                    if ($error == UPLOAD_ERR_OK)
                    {
                        $temp_file_pathname = $_FILES["zipfiles"]["tmp_name"][$key];

                        // We use basename() on the file name as it could help prevent filesystem traversal attacks
                        $extension              = strtolower(pathinfo($target_filename, PATHINFO_EXTENSION) );

                        // TODO validate the extension
                        $target_pathname        = "$target_dir/$target_filename";

                        // If the target file exists, replace it
                        if (file_exists($target_pathname) )
                        {
                            unlink($target_pathname);
                        }
                        if (move_uploaded_file($temp_file_pathname, $target_pathname) )
                        {
                            $filenames[] = $target_pathname;
                        }
                    }
                    else
                    {
                        echo "Unable to upload $target_filename. Error code $error<br>";
                    }
                }


                $db                         = new db_credentials();
                $reports_table              = new Reports($db);

                $results                    = new DatabaseRebuildResults;

                // Iterate $filenames; extract and import the resultant CSV files. Skip any records without a UID
                foreach ($filenames as $pathname)
                {
                    $za = new ZipArchive();

                    echo "Checking $pathname<br>";

                    $fileext = pathinfo($pathname, PATHINFO_EXTENSION);

                    if (0 == strcasecmp('zip', $fileext) )
                    {
                        extract_zipfile($pathname);

                        $za->open($pathname);

                        $files_to_import = array();

                        for($i = 0; $i < $za->numFiles; $i++ )
                        {
                            $stat = $za->statIndex( $i );

                            $archived_filename = $stat['name'];

                            echo "&nbsp;&nbsp;&nbsp;&nbsp;$archived_filename<br>";

                            $fileext = pathinfo($archived_filename, PATHINFO_EXTENSION);

                            if (0 == strcasecmp('csv', $fileext) )
                            {
                                $files_to_import[] = $archived_filename;
                            }
                        }


                        foreach ($files_to_import as $file_to_import)
                        {
                            echo("Importing data from $file_to_import...<br>");

                            $csv_items = read_csv_file("$target_dir/$file_to_import");

                            $results_for_file = ReportsImporter::add_csv_items($csv_items, $reports_table, null);

                            $results->add($results_for_file);
                        }
                    }
                }

                $caption = raw_get_host().' - reports imported';

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

                echo "$caption<br>";

                echo '<br><br><a href="#top">[Back to top</a>]';

                if (!empty($html) )
                {
                    echo '<br><hr><br>'.$html;

                    echo '<br><br>[<a href="#top">Back to top</a>]<br>';
                }
            }
        }

        echo   '</div>';
    }



?>
