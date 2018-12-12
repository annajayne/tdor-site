<?php
    /**
     * Generate and copy text for tweets about the current Report(s).
     *
     */

    function get_tweet_text($report)
    {
        $newline    = "\n";

        $url        = get_host().get_permalink($report);
        $date       = get_display_date($report);
        $cause      = get_displayed_cause_of_death($report);
        $place      = $report->has_location() ? "$report->location ($report->country)" : $report->country;

        $text       = $report->name;

        $text      .= " $cause";
        $text      .= " in $place";
        $text      .= " on $date.";

        if (!empty($report->age) )
        {
            $text  .= $newline.$newline."They were $report->age.";
        }

        if (is_logged_in() )
        {
            $text  .=  ' #SayTheirName #TransLivesMatter #TDoR';
        }

        $text      .=  $newline.$newline.$url.$newline;

        $text      .=  $newline.'[...]'.$newline.$newline;

        return $text;
    }


    require_once('models/report.php');
    require_once('controllers/reports_controller.php');


    // Retrieve data on the report(s) to export.
    $controller         = new ReportsController();
    $params             = $controller->get_current_params();

    $ip             = $_SERVER['REMOTE_ADDR'].'_';

    if (strpos($ip, ':') !== false)
    {
        $ip = '';
    }

    $date           = date("Y-m-d\TH_i_s");

    $basename       = 'tdor_tweet_download';
    $filename       = $basename.'_'.$ip.$date;

    $root           = $_SERVER["DOCUMENT_ROOT"];
    $export_folder  = 'data/export';

    $pathname       = "$export_folder/$filename.txt";

    $text           = '';

    foreach ($params->reports as $report)
    {
        $text .= get_tweet_text($report);
    }

    $fp = fopen($pathname, 'w');

    if ($fp)
    {
        fwrite($fp, pack("CCC",0xef, 0xbb, 0xbf) );             // Add UTF-8 BOM
        fwrite($fp, $text);

        fclose($fp);

        header("Content-Description: File Transfer");
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename='" . basename($pathname) . "'");

        readfile($pathname);
    }

?>
