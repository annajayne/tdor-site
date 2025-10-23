<?php
    /**
     * Generate and copy text for tweets about the current Report(s).
     *
     */

    require_once('models/reports.php');
    require_once('views/display_utils.php');
    require_once('controllers/reports_controller.php');
    require_once('util/cleanup_export_files.php');


    // Retrieve data on the report(s) to export.
    $controller     = new ReportsController();
    $params         = $controller->get_current_params();

    $ip             = $_SERVER['REMOTE_ADDR'].'_';

    if (strpos($ip, ':') !== false)
    {
        $ip = '';
    }

    $newline        = "\n";

    $date           = gmdate("Y-m-d\TH_i_s");

    $basename       = 'tdor_tweet_download';
    $filename       = $basename.'_'.$ip.$date;

    $root           = $_SERVER["DOCUMENT_ROOT"];
    $export_folder  = 'data/export';

    $pathname       = "$export_folder/$filename.txt";

    $text           = '';

    foreach ($params->reports as $report)
    {
        $url        = get_host().get_permalink($report);

        $text      .= get_tweet_text($report).$newline.$newline.$url.$newline.$newline.'[...]'.$newline.$newline;
    }

    $fp = fopen($pathname, 'w');

    if ($fp)
    {
        fwrite($fp, pack("CCC",0xef, 0xbb, 0xbf) );             // Add UTF-8 BOM
        fwrite($fp, $text);

        fclose($fp);

        header("Content-Description: File Transfer");
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=" . basename($pathname) );

        readfile($pathname);

        cleanup_old_export_files();
    }

?>
