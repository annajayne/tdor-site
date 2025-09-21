<?php
    /**
     * Administrative command to generate QR code image files.
     *
     */
    require_once('models/db_utils.php');


    /**
     * Format a size in bytes appropriately based on its size.
     *
     * @param int    $size              The size to present.
     * @param string $precision         The number of digits of precision (default 2).
     * @return string                   A string representation of $size, e.g. '64.15 MB'.
     *
     * ref: https://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
     */
    function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = array('', 'K', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)].'B';
    }


    /**
     * Display a list of orphaned data files of a specific type and offer the option to clean them up.
     *
     * @param string $folder_path       The path of the folder
     * @param string $type              The type of the files contained in the folder (e.g. "photo" or "thumbnail").
     * @param array  $filename_uid_map  An array which maps filenames to the UIDs of the corresponding report.
     * @param string $action            The action to take (e.g. "delete").
     * @param string $item              The item to perform $action upon
     */
    function show_orphaned_files($folder_path, $type, $filename_uid_map, $action, $item = '')
    {
        $filenames = array();

        if (file_exists($folder_path) )
        {
            $filenames = scandir($folder_path);
        }

        $files_to_delete = false;

        echo '<br>';

        $total_file_size = 0;

        foreach ($filenames as $filename)
        {
            $pathname = $folder_path . '/' . $filename;

            if (filetype($pathname) != 'file')
            {
                continue;
            }

            if ($filename === 'readme.txt')
            {
                continue;
            }

            if (!array_key_exists($filename, $filename_uid_map) )
            {
                $file_size = filesize($pathname);

                $total_file_size += $file_size;

                $action_text = "[<a href='?target=cleanup&type=$type&item=$filename&cmd_action=delete'>Delete</a>]";

                if ($action === 'delete')
                {
                    $can_delete = empty($item) || ($item == $filename);

                    if ($can_delete)
                    {
                        unlink($pathname);

                        $action_text = ' deleted';
                    }

                    $files_to_delete = !empty($item) && ($item != $filename);
                }
                else
                {
                    $files_to_delete = true;
                }

                $file_size_string = formatBytes($file_size);

                echo "Orphaned $type file: <b>$filename</b> ($file_size_string) $action_text<br>";
            }
        }

        if ($files_to_delete)
        {
            $total_file_size_string = formatBytes($total_file_size);

            echo "<br>Total file size: <b>$total_file_size_string</b><br>";

            echo "<br><ul>[<a href='?target=cleanup&type=$type&cmd_action=delete'>Delete All</a>]</ul>";
        }
        else
        {
            echo "No orphaned $type files<br>";
        }
    }


    /**
     * Display a list of backup tables and offer the option to clean them up.
     *
     * @param string $action            The action to take (e.g. "delete").
     */
     function show_backup_tables($action)
     {
        $db                         = new db_credentials();

        $table_names                = get_reports_backup_table_names($db);

        echo '<br>';

        foreach ($table_names as $table_name)
        {
            $action_done = '';

            if ($action === 'delete')
            {
                drop_table($db, $table_name);

                $action_done = ' deleted';
            }

            echo "Backup database table <b>$table_name</b>$action_done<br>";
        }

        if (!empty($table_names) )
        {
            echo "<br><ul>[<a href='?target=cleanup&type=database&cmd_action=delete'>Delete All</a>]</ul>";
        }
        else
        {
            echo "No backup report tables<br>";
        }
     }


    /**
     * Display a list of orphaned data files and offer the option to clean them up.
     *
     */
    function data_cleanup()
    {
        $action = '';
        $type = '';
        $item = '';

        if (!empty($_GET['type']) )
        {
            $type = $_GET['type'];
        }
        if (!empty($_GET['item']) )
        {
            $item = $_GET['item'];
        }
        if (!empty($_GET['cmd_action']) )
        {
            $action = $_GET['cmd_action'];
        }

        $data_folder_path = get_root_path()."/data";

        if (file_exists($data_folder_path) )
        {
            $photos_folder_path             = "$data_folder_path/photos";
            $thumbnails_folder_path         = "$data_folder_path/thumbnails";
            $qrcodes_folder_path            = "$data_folder_path/qrcodes";
            $export_folder_path             = "$data_folder_path/export";

            // Cross-reference files in the above folders against the contents of the reports themselves, and identify any orphans
            require_once('models/reports.php');

            $db                             = new db_credentials();
            $reports_table                  = new Reports($db);

            $query_params                   = new ReportsQueryParams();

            $query_params->status           = ReportStatus::draft | ReportStatus::published;

            $reports                        = $reports_table->get_all($query_params);

            $photo_filename_uid_map         = array();
            $qrcode_filename_uid_map        = array();

            foreach ($reports as $report)
            {
                if (!empty($report->photo_filename) )
                {
                    $photo_filename_uid_map[$report->photo_filename] = $report->uid;
                }

                $additional_photo_filenames = get_image_filenames($report->description);

                if (!empty($additional_photo_filenames))
                {
                    // Handle any additional inline images
                    foreach ($additional_photo_filenames as $additional_photo_filename)
                    {
                        $photo_filename_uid_map[basename($additional_photo_filename)] = $report->uid;
                    }
                }

                $qrcode_filename_uid_map[$report->uid.'.png'] = $report->uid;
            }

            show_orphaned_files($photos_folder_path,     'photo',     $photo_filename_uid_map,  ($type == 'photo') ?     $action : '', $item);
            show_orphaned_files($thumbnails_folder_path, 'thumbnail', $photo_filename_uid_map,  ($type == 'thumbnail') ? $action : '', $item);
            show_orphaned_files($qrcodes_folder_path,    'qrcode',    $qrcode_filename_uid_map, ($type == 'qrcode') ?    $action : '', $item);
            show_orphaned_files($export_folder_path,     'export',    array(),                  ($type == 'export') ?    $action : '', $item);
            show_orphaned_files($data_folder_path,      'data',       array(),                  ($type == 'data') ?      $action : '', $item);
            show_backup_tables(                                                                 ($type == 'database') ?  $action : '');
        }
    }

?>
