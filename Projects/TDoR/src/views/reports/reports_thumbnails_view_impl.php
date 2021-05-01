<?php
    /**
     * Report view implementation.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_display_date()


    /**
     * Show thumbnails for the given reports.
     *
     * @param array $reports                An array containing the given reports.
     *
     */
    function show_thumbnails($reports)
    {
        foreach ($reports as $report)
        {
            echo '<div class="grid_4" width="100%">';

            echo '<div>';

            $photo_pathname = get_photo_pathname('');

            if (!empty($report->photo_filename) )
            {
                $photo_pathname = '/data/thumbnails/'.$report->photo_filename;
            }

            $url        = get_permalink($report);
            $date       = date_str_to_display_date($report->date);
            $cause      = get_displayed_cause_of_death($report);
            $place      = $report->has_location() ? "$report->location ($report->country)" : $report->country;

            $date       = str_replace(' ', '&nbsp;', $date);          // Replace spaces with non-breaking ones.

            $caption    = "<b><a href='$url'>$report->name</a></b>";
            $caption   .= ' '.$cause;
            $caption   .= " in $place.";
            $caption   .= ' <i>'.$date.'</i>';


            echo '<a href="'.$url.'">';
            echo '<img src="'.$photo_pathname.'" />';
            echo '<div style="height:3em;><a href="'.$url.'">';
            echo $caption;
            echo '</a></div>';
            echo '</div></div>';
        }
    }

?>