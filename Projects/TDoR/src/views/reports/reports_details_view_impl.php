<?php
    function show_menu_links_for_report($report)
    {
        $menuitems[]    = array( 'href' => get_permalink($report, 'export').'&sortby=date&sortup=1',
                                 'rel' => 'nofollow',
                                 'text' => 'Export');

        if (is_logged_in() )
        {
            $menuitems[] = array('href' => get_permalink($report, 'edit'),
                                 'rel' => 'nofollow',
                                 'text' => 'Edit');

            $menuitems[] = array('href' => 'javascript:void(0);',
                                 'onclick' => 'confirm_delete(\''.get_permalink($report, 'delete').'\');',
                                 'rel' => 'nofollow',
                                 'text' => 'Delete');
        }

        if (!empty($menuitems) )
        {
            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).' | ';
            }

            // Trim trailing delimiter
            $menu_html = substr($menu_html, 0, strlen($menu_html) - 2);

            echo '<div class="command_menu">[ '.$menu_html.']</div>';
        }
    }


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

        $display_location = htmlspecialchars($report->location, ENT_QUOTES, 'UTF-8');

        if ($report->country !== '')
        {
            $display_location .= ' ('.htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8').')';
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

                $photo_caption .= " [photo: $photo_source_text]";
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

        echo '<br>'.$desc;

        show_menu_links_for_report($report);

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