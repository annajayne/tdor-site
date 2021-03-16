<?php
    /**
     * "Import Blogposts" page implementation.
     *
     */

    require_once('models/blog_table.php');
    require_once('models/blog_events.php');
    require_once('util/blog_importer.php');



    /**
     * Import blogposts.
     *
     *    1.  Prompt for zipfile(s)
     *    2.  Extract into /blog/content folder
     *    3.  Parse the blog metadata (.ini) files and read the corresponding content (.md) files
     *    4.  Add/update the corresponding blogposts
     *    5.  Output a table showing what was added/updated
     */
    function import_blogposts()
    {
        echo '<script src="/js/upload_zipfiles.js"></script>';

        echo '<h2>Import Blogposts</h2><br>';

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
                $target_dir = "blog/content";

                $filenames = array();

                // TODO: the code in this loop is IDENTICAL to that used when importing reports. We should consider consolidating it.
                foreach ($_FILES["zipfiles"]["error"] as $key => $error)
                {
                    $target_filename = basename($_FILES["zipfiles"]["name"][$key]);

                    if ($error == UPLOAD_ERR_OK)
                    {
                        $temp_file_pathname  = $_FILES["zipfiles"]["tmp_name"][$key];

                        // We use basename() on the file name as it could help prevent filesystem traversal attacks
                        $extension          = strtolower(pathinfo($target_filename, PATHINFO_EXTENSION) );

                        // TODO validate the extension
                        $target_pathname    = "$target_dir/$target_filename";

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
                $blog_table                 = new BlogTable($db);

                $details                    = new DatabaseItemChangeDetails;

                // Iterate $filenames; extract and import the resultant CSV files. Skip any records without a UID
                foreach ($filenames as $pathname)
                {
                    $za = new ZipArchive();

                    echo "Checking $pathname<br>";

                    $fileext = pathinfo($pathname, PATHINFO_EXTENSION);

                    if (0 == strcasecmp('zip', $fileext) )
                    {
                        extract_zipfile($pathname, $target_dir);

                        $za->open($pathname);

                        $files_to_import = array();

                        for($i = 0; $i < $za->numFiles; $i++ )
                        {
                            $stat = $za->statIndex( $i );

                            $archived_filename = $stat['name'];

                            echo "&nbsp;&nbsp;&nbsp;&nbsp;$archived_filename<br>";

                            $fileext = pathinfo($archived_filename, PATHINFO_EXTENSION);

                            if (0 == strcasecmp('ini', $fileext) )
                            {
                                $files_to_import[] = $archived_filename;
                            }
                        }

                        $blogposts = array();

                        foreach ($files_to_import as $file_to_import)
                        {
                            $blogposts[] = read_blogpost_ini_file("$target_dir/$file_to_import");
                        }

                        $details = BlogImporter::import_blogposts($blogposts, $blog_table);
                    }
                }


                // Display a table giving details of what's changed
                $caption = raw_get_host().' - blogposts imported';

                ob_end_flush();

                $caption .= ' by '.get_logged_in_username();

                $html = BlogEvents::blogposts_changed($caption, $details->items_added, $details->items_updated, $details->items_deleted);

                echo $caption.'<br>';

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