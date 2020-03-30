<?php
    /**
     * Administrative command to generate thumbnails for reports.
     *
     */



    /**
     * Rebuild thumbnail image files.
     *
     */
    function rebuild_thumbnails()
    {
        require_once('models/reports.php');

        $reports = Reports::get_all();

        foreach ($reports as $report)
        {
            if (!empty($report->photo_filename) )
            {
                $thumbnail_pathname = get_photo_thumbnail_path($report->photo_filename);

                if (!file_exists($thumbnail_pathname) )
                {
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;Creating thumbnail for '.get_host().get_permalink($report).'<br>';

                    create_photo_thumbnail($report->photo_filename);
                }
            }
        }

        echo 'Thumbnails generated<br>';
    }


?>
