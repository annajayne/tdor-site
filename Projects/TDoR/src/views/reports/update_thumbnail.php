<?php
    /**
     * Update the thumbnail of the current report (admin command)
     *
     */
    require_once('views/pages/admin/admin_utils.php');

    if (is_admin_user() )
    {
        $db                         = new db_credentials();
        $reports_table              = new Reports($db);

        $url                        =  get_host().get_permalink($report);

        if (!empty($report->photo_filename))
        {
            $thumbnail_pathname = get_photo_thumbnail_path($report->photo_filename);

            if (file_exists($thumbnail_pathname))
            {
                unlink($thumbnail_pathname);

                echo "Deleted old thumbnail for $url<br>";
            }

            echo "Creating new thumbnail for $url<br>";

            create_photo_thumbnail($report->photo_filename);
        }
        else
        {
            echo "No changes required for $url<br>";
        }

        echo "<br><a href='$url'>Return to report</a>";
    }
    else
    {
        redirect_to('/account/login');
    }
?>


