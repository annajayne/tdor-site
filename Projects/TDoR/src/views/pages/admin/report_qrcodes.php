<?php
    /**
     * Administrative command to generate QR code image files.
     *
     */


    /**
     * Rebuild QR code image files.
     *
     */
    function rebuild_qrcodes()
    {
        require_once('models/reports.php');

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

?>
