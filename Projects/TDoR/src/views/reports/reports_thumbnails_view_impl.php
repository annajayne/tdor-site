<?php
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

            $caption    = "<b><a href='$url'>$report->name</a></b>";
            $caption   .= ' '.get_displayed_cause_of_death($report);
            $caption   .= " in $report->location ($report->country).";
            $caption   .= ' <i>'.get_display_date($report).'</i>';


            echo '<a href="'.$url.'">';
            echo '<img src="'.$photo_pathname.'" />';
            echo '<div style="height:3em;><a href="'.$url.'">';
            echo $caption;
            echo '</a></div>';
            echo '</div></div>';
        }
    }

?>