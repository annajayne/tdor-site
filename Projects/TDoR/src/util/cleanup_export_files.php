<?php
    // Cleanup export files older than the specified age in days.
    //

    require_once('util/misc.php');


    // Cleanup export files older than the specified age in days.
    //
    function cleanup_old_export_files()
    {
        $age_limit_mins = 15;

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

                //e.g. 'tdor_export_2025-10-19T18_50_26'
                $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);

                // e.g '2025-10-19T18_50_26'
                $timestamp = substr($filename_without_ext, -19);
                $timestamp = str_replace('_', ':', $timestamp);

                try
                {
                    $timestamp_format = "Y-m-d\TH:i:s";

                    // Calculate the age of the file in minutes. NOte that although this is an odd
                    // way to do it is's the only way I could get this to work that gave the correct
                    //  results with UTC.
                    $timezone = new DateTimeZone('UTC');
                    $x = gmdate($timestamp_format);

                    $creation_time = DateTime::createFromFormat($timestamp_format, $timestamp, $timezone);

                    $now = DateTime::createFromFormat($timestamp_format, $x, $timezone);

                    $interval = $now->diff($creation_time);

                    $age_in_mins = $interval->i + ($interval->h * 60) + ($interval->d * 24 * 60);

                    if ($age_in_mins >= $age_limit_mins)
                    {
                        $pathname = $export_folder_path.'/'.$filename;
                        unlink($pathname);
                    }
                }
                catch (Exception $e)
                {
                    // Just in case - swallow the error and move onto the next file
                }
            }
        }
    }

?>
