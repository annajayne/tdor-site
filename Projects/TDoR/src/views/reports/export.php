<?php
    /**
     * View to export and download the displayed reports.
     * 
     * The controller uses the model below to query the database. See ReportsController::show() and ReportsController::index() for details.
     */


    require_once('models/reports.php');
    require_once('controllers/reports_controller.php');
    require_once('util/reports_exporter.php');

    if (!is_bot(get_user_agent() ) )
    {
        // Retrieve data on the report(s) to export.
        $controller         = new ReportsController();

        $params             = $controller->get_current_params();


        // Generate the export file
        //
        $exporter           = new ReportsExporter($params->reports);

        $ip                 = $_SERVER['REMOTE_ADDR'].'_';

        if (strpos($ip, ':') !== false)
        {
            $ip = '';
        }

        $date               = date("Y-m-d\TH_i_s");

        $basename          = 'tdor_export';
        $filename           = $basename.'_'.$ip.$date;

        $root               = $_SERVER["DOCUMENT_ROOT"];
        $export_folder      = 'data/export';

        $csv_file_pathname  = "$export_folder/$filename.csv";
        $zip_file_pathname  = "$root/$export_folder/$filename.zip";

        $exporter->write_csv_file($csv_file_pathname);
        $exporter->create_zip_archive($zip_file_pathname, $csv_file_pathname, "$basename.csv");

        ob_clean();
        ob_end_flush(); // Needed as otherwise Windows will report the zipfile to be corrupted (see https://stackoverflow.com/questions/13528067/zip-archive-sent-by-php-is-corrupted/13528263#13528263)

     //   unlink($csv_file_pathname);

        // Download the export file
        header("Content-Description: File Transfer");
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=" . basename($zip_file_pathname) );

        readfile($zip_file_pathname);

        exit();
    }
?>
