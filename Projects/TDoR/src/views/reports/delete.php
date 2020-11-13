<?php
    /**
     * Delete the current Report.
     *
     */

    require_once('models/report_utils.php');
    require_once('models/report_events.php');


    if (is_editor_user() )
    {
        $db              = new db_credentials();
        $reports_table   = new Reports($db);

        if ($reports_table->delete($report) )
        {
            ReportEvents::report_deleted($report);

            $report_descriptor = "$report->name / ".date_str_to_display_date($report->date)." / $report->location ($report->country)";

            echo "Report $report_descriptor deleted";
        }
    }

?>
