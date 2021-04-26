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
                $content_folder_path    = 'blog/content';
                $import_folder_path     = "$content_folder_path/import";

                $db                     = new db_credentials();
                $blog_table             = new BlogTable($db);

                $importer               = new BlogImporter($blog_table, $content_folder_path, $import_folder_path);

                $zipfile_pathnames      = $importer->upload_zipfiles($import_folder_path);

                $details                = new DatabaseItemChangeDetails;

                // Extract and import the uploaded zipfiles.
                foreach ($zipfile_pathnames as $zipfile_pathname)
                {
                    $file_details       = $importer->import_zipfile($zipfile_pathname);

                    $details->add($file_details);
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