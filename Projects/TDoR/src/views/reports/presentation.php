<?php
    /**
     * "Download slides" page.
     *
     */


    require_once('models/reports.php');
    require_once('views/display_utils.php');
    require_once('controllers/reports_controller.php');
    require_once('util/presentation_exporter.php');



    /**
     * Determine an appropriate subtitle for a presentation representing the given parameters.
     *
     * @param array $params         The parameters the reports are selected against
     */
    function get_subtitle($params)
    {
        $subtitle = '';

        if (str_ends_with($params->date_from_str, '-10-01') && str_ends_with($params->date_to_str, '-09-30') )
        {
            $tdor_year_from         = (int)strval(get_tdor_year(new DateTime($params->date_from_str) ) );
            $tdor_year_to           = (int)strval(get_tdor_year(new DateTime($params->date_to_str) ) );

            if ($tdor_year_from == $tdor_year_to)
            {
                $year          = $tdor_year_to;

                $subtitle       = "Trans Day of Remembrance $year";
            }
        }
        return $subtitle;
    }


    // Retrieve data on the report(s) to export.
    $controller     = new ReportsController();
    $params         = $controller->get_current_params();

    $ip             = $_SERVER['REMOTE_ADDR'].'_';

    if (strpos($ip, ':') !== false)
    {
        $ip = '';
    }

    $newline        = "\n";

    $date           = date("Y-m-d\TH_i_s");

    $basename       = 'tdor_slides';
    $filename       = $basename.'_'.$ip.$date;

    $root           = $_SERVER["DOCUMENT_ROOT"];
    $export_folder  = '/data/export';

    $pathname       = get_root_path()."$export_folder/$filename.pptx";       // Supports .pptx, .odp or pphpt (the latter is a direct serialisation of PHPPresentation)

    $presenter = new PresentationExporter();

    if (isset($_GET['qrcodes']) )
    {
        $presenter->show_qrcodes = ($_GET['qrcodes'] != 0) ? true : false;
    }

    $presenter->initialise();

    $presenter->subtitle = get_subtitle($params);


    $presenter->generate($params->reports);

    $presenter->save($pathname);

    if (true)
    {
        header("Content-Description: File Transfer");
        header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=" . basename($pathname) );

        readfile($pathname);
    }

?>