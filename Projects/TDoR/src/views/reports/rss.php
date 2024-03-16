<?php
    /**
     * RSS feed implementation.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_iso() and date_str_to_display_date()
    require_once('util/utils.php');
    require_once('models/reports.php');
    require_once('controllers/reports_controller.php');


    // Retrieve data on the report(s) to export.
    $controller         = new ReportsController();
    $params             = $controller->get_current_params();

    $host  = get_host();
    $email = 'tdor@translivesmatter.info';

    $description        = 'Remembering trans people lost to violence or suicide';
    $link               = "$host/reports?";

    $title_suffix       = '';

    if (!empty($params->date_from_str) && !empty($params->date_to_str) )
    {
        $link           .= "from=".date_str_to_iso($params->date_from_str)."&";
        $link           .= "to=".date_str_to_iso($params->date_to_str)."&";

        $title_suffix   = " from: $params->date_from_str; to: $params->date_to_str;";
    }

    if (!empty($params->country) )
    {
        $title_suffix   .= " country: $params->country;";
    }

    if (!empty($params->filter) )
    {
        $title_suffix   .= " filtered by: $params->filter;";
    }

    $link               .= "country=$params->country&";
    $link               .= "filter=$params->filter&";
    $link               .= "view=list";

    $link               = htmlentities($link);

    header("Content-Type: application/xml; charset=UTF-8");

    $title              = 'Remembering Our Dead';

    if (DEV_INSTALL)
    {
        $title          .= ' [DEV]';
    }

    if (!empty($title_suffix) )
    {
        $title          .= " -$title_suffix";
    }

    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    echo '<rss version="2.0">'."\n";
    echo   "<channel>\n";
    echo     "<title>$title</title>\n";
    echo     "<link>$link</link>\n";
    echo     "<description>$description</description>\n";
    echo     "<language>en-us</language>\n";

    echo     "<image>\n";
    echo       "<url>$host/images/tdor_candle_jars.jpg</url>\n";
    echo       "<title>$title</title>\n";
    echo       "<link>$host</link>\n";
    echo     "</image>\n";

    foreach ($params->reports as $report)
    {
        $date                   = date_str_to_display_date($report->date);
        $category               = ($report->category !== $report->cause) ? "$report->category/$report->cause" : $report->cause;
        $cause                  = get_displayed_cause_of_death($report);
        $place                  = $report->has_location() ? "$report->location, $report->country" : $report->country;
        $photo_pathname         = $host.get_thumbnail_pathname($report->photo_filename);
        $qrcode_pathname        = "/data/qrcodes/$report->uid.png";
        $permalink              = $host.get_permalink($report);

        $date_created           = !empty($report->date_created) ? $report->date_created : date("Y-m-d H:i:s");
        $pub_date               = !empty($report->date_updated) ? $report->date_updated : $date_created;

        $title                  = "$report->name ($date - $place)";

        $description             = "<h2><a href='$permalink'>$report->name</a></h2><br>";

        if ($report->age !== '')
        {
            $description        .= "Age $report->age<br>";
        }

        $display_location       = htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8');

        if ($report->has_location() )
        {
            $display_location   = htmlspecialchars($report->location, ENT_QUOTES, 'UTF-8');

            $display_location   .= ' ('.htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8').')';
        }

        $description            .= date_str_to_display_date($report->date).'<br>'.$display_location.'<br>';

        $description            .= ucfirst($report->cause).'<br><br>';

        $description            .= get_short_description($report);

        echo '<item>';
        echo   "<title>$title</title>";
        echo   "<author>$email</author>";

        if (!empty($report->uid) )
        {
            echo "<guid>$report->uid</guid>";
        }

        echo   "<link>$permalink</link>";
        echo   "<category>$category</category>";
        echo   "<description><![CDATA[$description]]></description>";
        echo   "<enclosure url='$photo_pathname' type='image/jpeg' />";
        echo   "<pubDate>$pub_date</pubDate>";
        echo "</item>\n";
    }

    echo   "</channel>\n";
    echo "</rss>\n";
?>
