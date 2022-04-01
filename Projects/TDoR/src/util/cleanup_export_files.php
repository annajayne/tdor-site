<?php
    // Cleanup export files older than the specified age in days.
    //

    require_once('util/misc.php');


    // Cleanup export files older than the specified age in days.
    //
    function cleanup_old_export_files()
    {
        $age_limit = 1;

        $export_folder_path = get_root_path()."/data/export";

        if (file_exists($export_folder_path) )
        {
            $current_date = new DateTime();

            $filenames = scandir($export_folder_path);

            foreach ($filenames as $filename)
            {
                if ( ($filename === '.') || ($filename === '..') || ($filename === 'readme.txt') )
                {
                    continue;
                }

                $date_components    = date_parse($filename);

                $day                = $date_components['day'];
                $month              = $date_components['month'];
                $year               = $date_components['year'];

                if ( ($year > 0) && ($month > 0) && ($day > 0) )
                {
                    $file_date      = new DateTime("$year-$month-$day");

                    $age_in_days    = date_diff($current_date, $file_date)->days;

                    if ($age_in_days >= $age_limit)
                    {
                        $pathname = $export_folder_path.'/'.$filename;
                        unlink($pathname);
                    }
                }
            }
        }
    }

?>
