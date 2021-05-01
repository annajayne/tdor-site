<?php
    /**
     * Report events.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_iso() and date_str_to_display_date()


    /**
     * Class to handle Report events.
     *
     */
    class ReportEvents
    {

        /** @var string                  The (relative) path to the folder which should hold zipfiles of change summaries. */
        private static $export_folder = 'data/edits';

        /** @var string                  A newline. */
        private static $newline = "\n";



        /**
         * Implementation method to send an email notification.
         *
         * @param string $html                  The HTML text of the email to send, *without* <html> and <body> tags.
         */
        private static function report_email_notify($html)
        {
            $subject = 'tdor.translivesmatter.info report change notification';

            send_email(SENDER_EMAIL_ADDRESS, NOTIFY_EMAIL_ADDRESS, $subject, $html);
        }


        /**
         * Implementation method to get a single line of an HTML table giving details of the changes made to a report.
         *
         * @param Report $report                The affected report.
         * @param string $verb                  The type of change (e.g. added, deleted or updated).
         * @param string $zip_file_url          The url of a zipfile containing details.
         * @return string                       HTML text.
         */
        public static function get_report_change_details_html($report, $verb, $zip_file_url)
        {
            $report_url   = raw_get_host().get_permalink($report);

            $iso_date     = date_str_to_iso($report->date);
            $place        = $report->has_location() ? $report->location : '-';

            $details      = ($verb == 'deleted') ? "<b>$verb</b>" : $verb;

            if (!empty($zip_file_url) )
            {
                $details .= " [<a href='$zip_file_url'>Details</a>]";
            }

            $qualifier    = $report->draft ? ' [DRAFT]' : '';

            $html         = '<tr>';
            $html        .= "<td style='white-space: nowrap;' sorttable_customkey='$iso_date'>". date_str_to_display_date($report->date).'</td>';
            $html        .= "<td><a href='$report_url'>$report->name</a>$qualifier</td>";
            $html        .= "<td align='center'>$report->age</td>";
            $html        .= "<td>$place</td>";
            $html        .= "<td>$report->country</td>";
            $html        .= "<td>$report->cause</td>";
            $html        .= "<td>$details</td>";

            $html        .= '</tr>'.self::$newline;

            return $html;
        }


        /**
         * Return the base filename of a logfile to give details of the reports affected by a change.
         *
         * @param string $username              The name of the user who made the change.
         * @param string $verb                  The type of change (e.g. added, deleted or updated).
         * @param array $reports                An array containing the reports to export.
         * @return string                       The filename, without the extension.
         */
        private static function get_change_summary_file_name($username, $verb, $reports)
        {
            $ip                 = $_SERVER['REMOTE_ADDR'].'_';

            if (strpos($ip, ':') !== false)
            {
                $ip = '';
            }

            $date               = date("Y-m-d\TH_i_s");

            $filename           = $date.'_'.$ip.'_'.$verb;

            if (!empty($username) )
            {
                $filename       .= '_'.$username;
            }

            if (count($reports) === 1)
            {
                $report         = $reports[0];

                // Simplify the "name" field by replacing accented characters with ASCII equivalents,
                // stripping out non-alphanumeric chars and replacing spaces with hypthens
                $name               = trim(replace_accents($report->name) );
                $name               = preg_replace('/[^[a-zA-Z0-9- ]/', '', $name);
                $name               = str_replace(' ', '-', $name);

                $filename       .= '_'.$name;

                $filename       .= ' ('.date_str_to_display_date($report->date).')';
            }
            else
            {
                $filename       .= '_('.count($reports).' reports)';
            }
            return $filename;
        }


        /**
         * Get the text of an HTML table giving details of the reports either added, changed or deleted.
         *
         * @param array $reports                An array of reports which were affected.
         * @param string $verb                  The action performed, e.g. "added".
         * @param string $zip_file_url          The url of a zipfile containing details.
         * @return string                       HTML text.
         */
        public static function get_reports_change_details_html($reports, $verb, $zip_file_url)
        {
            $html_rows = array();

            foreach($reports as $report)
            {
                $html_rows[] .= self::get_report_change_details_html($report, $verb, $zip_file_url);
            }
            return $html_rows;
        }


        /**
         * Get the text of HTML tables giving details of the reports affected by a change.
         *
         * @param string $caption               The nature of the change, e.g. "Database rebuilt"
         * @param array $reports_added          An array of reports which were added.
         * @param array $reports_updated        An array of reports which were updated.
         * @param array $reports_deleted        An array of reports which were deleted.
         * @param string $zip_file_url_added    The url of a zipfile containing details of the added reports.
         * @param string $zip_file_url_updated  The url of a zipfile containing details of the changed reports.
         * @param string $zip_file_url_deleted  The url of a zipfile containing details of the deleted reports.
         * @return string                       HTML text.
         */
        private static function get_change_details_html($caption, $reports_added, $reports_updated, $reports_deleted, $zip_file_url_added, $zip_file_url_updated, $zip_file_url_deleted)
        {
            $reports_changed = !empty($reports_added) || !empty($reports_updated) || !empty($reports_deleted);

            $html = '<div>';

            $caption .= ' - '.($reports_changed ? 'Details of changes' : 'No changes made');

            $html .= "<h2><a name='change_details'>$caption</a></h2>";

            if ($reports_changed)
            {
                $html_rows = array();

                if (!empty($reports_added) )
                {
                    $html_rows = array_merge($html_rows, self::get_reports_change_details_html($reports_added, 'added', $zip_file_url_added) );
                }
                if (!empty($reports_updated) )
                {
                    $html_rows = array_merge($html_rows, self::get_reports_change_details_html($reports_updated, 'updated', $zip_file_url_updated) );
                }
                if (!empty($reports_deleted) )
                {
                    $html_rows = array_merge($html_rows, self::get_reports_change_details_html($reports_deleted, 'deleted', $zip_file_url_deleted) );
                }

                $html .= '<table class="sortable" border="1" rules="all" style="border-color: #666;" cellpadding="10">';
                $html .= '<tr><th>Date</th><th>Name</th><th align="center">Age</th><th>Location</th><th>Country</th><th>Cause</th><th>Details</th></tr>';

                foreach ($html_rows as $html_row)
                {
                    $html .= $html_row;
                }

                $html .= "</table>";
            }

            $html .= "</div>";

            return $html;
        }


        /**
         * Report added event.
         *
         * This event is fired when an editor adds a single new report.
         *
         * @param Report $report          The report which has been added.
         */
        public static function report_added($report)
        {
            $reports_added = array();
            $reports_added[] = $report;

            $caption = raw_get_host().' - report added by '.get_logged_in_username();

            self::reports_changed($caption, $reports_added, null, null);
        }


        /**
         * Report updated event.
         *
         * This event is fired when an editor updates a single existing report.
         *
         * @param Report $report          The report which has been updated.
         */
        public static function report_updated($report)
        {
            $reports_updated = array();
            $reports_updated[] = $report;

            $caption = raw_get_host().' - report edited by '.get_logged_in_username();

            self::reports_changed($caption, null, $reports_updated, null);
        }


        /**
         * Report deleted event.
         *
         * This event is fired when an editor deletes a single existing report.
         *
         * @param Report $report          The report which has been deleted.
         */
        public static function report_deleted($report)
        {
            $reports_deleted = array();
            $reports_deleted[] = $report;

            $caption = raw_get_host().' - report deleted by '.get_logged_in_username();

            self::reports_changed($caption, null, null, $reports_deleted);
        }


        /**
         * Reports changed event.
         *
         * This event is fired when an administrator executes a database rebuild operation
         *
         * @param string $caption         A string describing the action performed, and by who.
         * @param Report $reports_added   An array of the reports which have been added.
         * @param Report $reports_updated An array of the reports which have been updated.
         * @param Report $reports_deleted An array of reports which have been deleted.
         * @param Report $report          The report which has been deleted.
         */
        public static function reports_changed($caption, $reports_added, $reports_updated, $reports_deleted)
        {
            $username = get_logged_in_username();

            $zip_file_url_added    = '';
            $zip_file_url_updated  = '';
            $zip_file_url_deleted  = '';

            $host = get_host();

            if (!empty($reports_added) )
            {
                $filename = self::get_change_summary_file_name($username, 'added', $reports_added);

                $zip_file_url_added = $host.'/'.ReportUtils::create_export_zipfile($reports_added, $filename, self::$export_folder);
            }

            if (!empty($reports_updated) )
            {
                $filename = self::get_change_summary_file_name($username, 'updated', $reports_updated);

                $zip_file_url_updated = $host.'/'.ReportUtils::create_export_zipfile($reports_updated, $filename, self::$export_folder);
            }

            if (!empty($reports_deleted) )
            {
                $filename = self::get_change_summary_file_name($username, 'deleted', $reports_deleted);

                $zip_file_url_deleted = $host.'/'.ReportUtils::create_export_zipfile($reports_deleted, $filename, self::$export_folder);
            }

            $html = self::get_change_details_html($caption, $reports_added, $reports_updated, $reports_deleted, $zip_file_url_added, $zip_file_url_updated, $zip_file_url_deleted);

            // Notify the site admins that a change has been made.
            self::report_email_notify($html);

            return $html;
        }
    }


?>
