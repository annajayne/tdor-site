
<script type="text/javascript">
    // Delete confirmation prompt
    //
    function confirm_delete(url)
    {
        var result = confirm("Delete this report?");

        if (result)
        {
            window.location.href = url;

            return true;
        }
        return false;
    }
</script>


<?php

    function show_report($report, $link_url = '')
    {
        $heading = $report->name;

        if ($report->deleted)
        {
            $heading .= ' [Deleted]';
        }

        $heading = "<h2>$heading</h2>";

        if ($link_url !== '')
        {
            $heading = "<a href='$link_url'>$heading</a>";
        }


        $summary = $heading;

        if ($report->age !== '')
        {
            $summary .= "Age $report->age<br>";
        }

        $display_location = htmlentities($report->location);

        if ($report->country !== '')
        {
            $display_location .= ' ('.htmlentities($report->country).')';
        }

        $summary .= get_display_date($report).'<br>'.
                    $display_location.'<br>';

        if ($report->tgeu_ref !== '')
        {
            $summary .= "TGEU ref: $report->tgeu_ref<br>";
        }

        $summary .= ucfirst($report->cause).'<br>';

        echo "<br><p>$summary</p>";

        $photo_pathname = get_photo_pathname($report->photo_filename);
        $photo_caption  = '';

        if ($report->photo_filename !== '')
        {
            $photo_caption  = $report->name;

            if ($report->photo_source !== '')
            {
                $photo_source_text = get_photo_source_text($report->photo_source);

                $photo_caption .= (" [photo: $photo_source_text]");
            }
        }

        // Dispay the photo and caption
        echo "<div class='photo_caption''>";
        echo   "<img src='".$photo_pathname."' alt='".$report->name."' /><br>";
        echo   $photo_caption.'<br>';
        echo "</div>";

        // Convert line breaks to paragraphs and expand any links
        $desc = markdown_to_html($report->description);
        $desc = linkify($desc, array('http', 'mail'), array('target' => '_blank') );

        echo "<br>$desc";

        if (ALLOW_REPORT_EDITING)
        {
            echo '<div align="right">[ ';
            echo   '<a href="'.get_permalink($report, 'edit').'">Edit</a> | ';
            echo   '<a onclick="confirm_delete(\''.get_permalink($report, 'delete').'\');" href="javascript:void(0);">Delete</a>';
            echo ']</div>';
        }

        show_social_links_for_report($report);
    }


    function show_details($reports)
    {
        foreach ($reports as $report)
        {
            show_report($report, get_permalink($report) );
            echo '<hr>';
        }
    }

?>