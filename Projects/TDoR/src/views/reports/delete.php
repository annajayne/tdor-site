<?php

    if (is_logged_in() )
    {
        Reports::delete($report);

        $report_descriptor = "$report->name / ".date_str_to_display_date($report->date)." / $report->location ($report->country)";

        echo "Report $report_descriptor deleted";
    }

?>
