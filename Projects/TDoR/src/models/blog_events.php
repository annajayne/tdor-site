<?php
    /**
     * Blog events.
     *
     */
    require_once('util/blog_utils.php');                // For get_blog_content_folder()
    require_once('util/blog_exporter.php');


    /**
     * Class to handle Blog events.
     *
     */
    class BlogEvents
    {
        /** @var string                         A newline. */
        private static $newline = "\n";


        /**
         * Implementation method to send an email notification.
         *
         * @param string $html                  The HTML text of the email to send, *without* <html> and <body> tags.
         */
        private static function blogpost_email_notify($html)
        {
            $subject = 'tdor.translivesmatter.info blogpost change notification';

            send_email(SENDER_EMAIL_ADDRESS, NOTIFY_EMAIL_ADDRESS, $subject, $html);
        }


        /**
         * Implementation method to get a single line of an HTML table giving details of the changes made to a blogpost.
         *
         * @param Report $blogpost              The affected blogpost.
         * @param string $verb                  The type of change (e.g. added, deleted or updated).
         * @param string $zip_file_url          The url of a zipfile containing details.
         * @return string                       HTML text.
         */
        public static function get_blogpost_change_details_html($blogpost, $verb, $zip_file_url)
        {
            $username       = get_logged_in_username();

            $blogpost_url   = raw_get_host().$blogpost->permalink;

            $iso_date       = $blogpost->timestamp;

            $details        = ( ($verb == 'deleted') || ($verb == 'purged') ) ? "<b>$verb</b>" : $verb;

            if (!empty($zip_file_url) )
            {
                $details    .= " [<a href='$zip_file_url'>Details</a>]";
            }

            $qualifier      = $blogpost->draft ? ' [DRAFT]' : '';

            $html           = '<tr>';
            $html           .= "<td><a href='$blogpost_url'>$blogpost->title</a>$qualifier</td>";
            $html           .= "<td style='white-space: nowrap;' sorttable_customkey='$iso_date'>".$blogpost->timestamp.'</td>';
            $html           .= "<td>$username</td>";
            $html           .= "<td>$details</td>";

            $html           .= '</tr>'.self::$newline;

            return $html;
        }


        /**
         * Return the base filename of a logfile to give details of the blogposts affected by a change.
         *
         * @param string $username              The name of the user who made the change.
         * @param string $verb                  The type of change (e.g. added, deleted, updated or purged).
         * @param array $blogposts              An array containing the blogposts to export.
         * @return string                       The filename, without the extension.
         */
        private static function get_change_summary_file_name($username, $verb, $blogposts)
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

            if (count($blogposts) === 1)
            {
                $blogpost       = $blogposts[0];

                // Simplify the "name" field by replacing accented characters with ASCII equivalents,
                // stripping out non-alphanumeric chars and replacing spaces with hypthens
                $title          = trim(replace_accents($blogpost->title) );
                $title          = preg_replace('/[^[a-zA-Z0-9- ]/', '', $title);
                $title          = str_replace(' ', '-', $title);

                $filename       .= '_'.$title;

                $filename       .= ' ('.date_str_to_display_date($blogpost->timestamp).')';
            }
            else
            {
                $filename       .= '_('.count($blogposts).' blogposts)';
            }
            return $filename;
        }


        /**
         * Get the text of an HTML table giving details of the blogposts either added, changed, deleted or purged.
         *
         * @param array $blogposts              An array of blogposts which were affected.
         * @param string $verb                  The action performed, e.g. "added".
         * @param string $zip_file_url          The url of a zipfile containing details.
         * @return string                       HTML text.
         */
        public static function get_blogposts_change_details_html($blogposts, $verb, $zip_file_url)
        {
            $html_rows = array();

            foreach($blogposts as $blogpost)
            {
                $html_rows[] .= self::get_blogpost_change_details_html($blogpost, $verb, $zip_file_url);
            }
            return $html_rows;
        }


        /**
         * Get the text of HTML tables giving details of the blogposts affected by a change.
         *
         * @param string $caption               The nature of the change, e.g. "Database rebuilt"
         * @param array $blogposts_added        An array of blogposts which were added.
         * @param array $blogposts_updated      An array of blogposts which were updated.
         * @param array $blogposts_deleted      An array of blogposts which were deleted.
         * @param array $blogposts_purged       An array of blogposts which were purged.
         * @param string $zip_file_url_added    The url of a zipfile containing details of the added blogposts.
         * @param string $zip_file_url_updated  The url of a zipfile containing details of the changed blogposts.
         * @param string $zip_file_url_deleted  The url of a zipfile containing details of the deleted blogposts.
         * @param string $zip_file_url_purged   The url of a zipfile containing details of the purged blogposts.
         * @return string                       HTML text.
         */
        private static function get_change_details_html($caption, $blogposts_added, $blogposts_updated, $blogposts_deleted,  $blogposts_purged, $zip_file_url_added, $zip_file_url_updated, $zip_file_url_deleted, $zip_file_url_purged)
        {
            $blogposts_changed = !empty($blogposts_added) || !empty($blogposts_updated) || !empty($blogposts_deleted)|| !empty($blogposts_purged);

            $html = '<div>';

            $caption .= ' - '.($blogposts_changed ? 'Details of changes' : 'No changes made');

            $html .= "<h2><a name='change_details'>$caption</a></h2>";

            if ($blogposts_changed)
            {
                $html_rows = array();

                if (!empty($blogposts_added) )
                {
                    $html_rows = array_merge($html_rows, self::get_blogposts_change_details_html($blogposts_added, 'added', $zip_file_url_added) );
                }
                if (!empty($blogposts_updated) )
                {
                    $html_rows = array_merge($html_rows, self::get_blogposts_change_details_html($blogposts_updated, 'updated', $zip_file_url_updated) );
                }
                if (!empty($blogposts_deleted) )
                {
                    $html_rows = array_merge($html_rows, self::get_blogposts_change_details_html($blogposts_deleted, 'deleted', $zip_file_url_deleted) );
                }
                if (!empty($blogposts_purged) )
                {
                    $html_rows = array_merge($html_rows, self::get_blogposts_change_details_html($blogposts_purged, 'purged', $zip_file_url_purged) );
                }

                $html .= '<table class="sortable" border="1" rules="all" style="border-color: #666;" cellpadding="10">';
                $html .= '<tr><th>Title</th><th>Date</th><th>Author</th><th>Details</th></tr>';

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
         * This event is fired when an editor adds a single new blogpost.
         *
         * @param Report $blogpost              The blogpost which has been added.
         */
        public static function blogpost_added($blogpost)
        {
            $blogposts_added        = array();
            $blogposts_added[]      = $blogpost;

            $caption = raw_get_host().' - blogpost added by '.get_logged_in_username();

            self::blogposts_changed($caption, $blogposts_added, null, null, null);
        }


        /**
         * Report updated event.
         *
         * This event is fired when an editor updates a single existing blogpost.
         *
         * @param Report $blogpost              The blogpost which has been updated.
         */
        public static function blogpost_updated($blogpost)
        {
            $blogposts_updated      = array();
            $blogposts_updated[]    = $blogpost;

            $caption = raw_get_host().' - blogpost edited by '.get_logged_in_username();

            self::blogposts_changed($caption, null, $blogposts_updated, null, null);
        }


        /**
         * Report deleted event.
         *
         * This event is fired when an editor deletes a single existing blogpost.
         *
         * @param Report $blogpost              The blogpost which has been deleted.
         */
        public static function blogpost_deleted($blogpost)
        {
            $blogposts_deleted      = array();
            $blogposts_deleted[]    = $blogpost;

            $caption = raw_get_host().' - blogpost deleted by '.get_logged_in_username();

            self::blogposts_changed($caption, null, null, $blogposts_deleted, null);
        }



        /**
         * Report deleted event.
         *
         * This event is fired when an admin purges a single existing blogpost.
         *
         * @param Report $blogpost              The blogpost which has been purged.
         */
        public static function blogpost_purged($blogpost)
        {
            $blogposts_purged      = array();
            $blogposts_purged[]    = $blogpost;

            $caption = raw_get_host().' - blogpost purged by '.get_logged_in_username();

            self::blogposts_changed($caption, null, null, null, $blogposts_purged);
        }


        /**
         * Reports changed event.
         *
         * This event is fired when an administrator executes a database rebuild operation
         *
         * @param string $caption           A string describing the action performed, and by who.
         * @param Report $blogposts_added   An array of the blogposts which have been added.
         * @param Report $blogposts_updated An array of the blogposts which have been updated.
         * @param Report $blogposts_deleted An array of blogposts which have been deleted.
         * @param Report $blogposts_purged  An array of blogposts which have been purged.
         * @param Report $blogpost          The blogpost which has been deleted.
         */
        public static function blogposts_changed($caption, $blogposts_added, $blogposts_updated, $blogposts_deleted, $blogposts_purged = [])
        {
            $host                   = get_host();

            $username               = get_logged_in_username();

            $export_folder          = get_blog_content_folder().'/edits';

            $zip_file_url_added     = '';
            $zip_file_url_updated   = '';
            $zip_file_url_deleted   = '';
            $zip_file_url_purged    = '';

            if (!empty($blogposts_added) )
            {
                $filename = self::get_change_summary_file_name($username, 'added', $blogposts_added);

                $zip_file_url_added = $host.'/'.self::create_blog_export_zipfile($blogposts_added, $filename, $export_folder);
            }

            if (!empty($blogposts_updated) )
            {
                $filename = self::get_change_summary_file_name($username, 'updated', $blogposts_updated);

                $zip_file_url_updated = $host.'/'.self::create_blog_export_zipfile($blogposts_updated, $filename, $export_folder);
            }

            if (!empty($blogposts_deleted) )
            {
                $filename = self::get_change_summary_file_name($username, 'deleted', $blogposts_deleted);

                $zip_file_url_deleted = $host.'/'.self::create_blog_export_zipfile($blogposts_deleted, $filename, $export_folder);
            }

            if (!empty($blogposts_purged) )
            {
                $filename = self::get_change_summary_file_name($username, 'purged', $blogposts_purged);

                $zip_file_url_purged = $host.'/'.self::create_blog_export_zipfile($blogposts_purged, $filename, $export_folder);
            }

            $html = self::get_change_details_html($caption, $blogposts_added, $blogposts_updated, $blogposts_deleted, $blogposts_purged, $zip_file_url_added, $zip_file_url_updated, $zip_file_url_deleted, $zip_file_url_purged);

            // Notify the site admins that a change has been made.
            self::blogpost_email_notify($html);

            return $html;
        }


        /**
         * Create an export zipfile.
         *
         * @param Blogpost $blogposts           The blogposts to export.
         * @param Blogpost $filename            The filename (without extension) of the export file.
         * @param Blogpost $export_folder       The path of the export folder.
         * @return boolean                      true if OK; false otherwise.
         */
        private static function create_blog_export_zipfile($blogposts, $filename, $export_folder)
        {
            $blog_content_folder    = get_blog_content_folder();
            $blog_media_folder      = "$blog_content_folder/media";

            $exporter               = new BlogExporter($blogposts, $blog_content_folder, $blog_media_folder);

            $root                   = $_SERVER["DOCUMENT_ROOT"];

            $zip_file_pathname      = "$export_folder/$filename.zip";

            $exporter->write_blogposts($export_folder);
            $exporter->create_zip_archive("$root/$zip_file_pathname");

            return $zip_file_pathname;
        }

    }

?>
