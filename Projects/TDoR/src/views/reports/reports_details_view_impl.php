<?php
    /**
     * Report view implementation.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_display_date()
    require_once('util/markdown_utils.php');                    // For markdown_to_html()
    require_once('util/openstreetmap.php');



   /**
     * Show the command menu links for the given report.
     *
     * @param Report $report                The given report.
     *
     */
    function show_menu_links_for_report($report)
    {
        $is_bot          = is_bot(get_user_agent() );

        $menuitems[]     = array('href' => get_permalink($report, 'memorial_card'),
                                 'rel' => 'nofollow',
                                 'target' => '_blank',
                                 'text' => 'Memorial Card');

        if (!$is_bot)
        {
            $menuitems[]     = array('href' => get_permalink($report, 'export').'&sortby=date&sortup=1',
                                     'rel' => 'nofollow',
                                     'text' => 'Download Data');
        }

        if (is_editor_user() )
        {
            $is_admin           = is_admin_user();

            $site_config        = get_config();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];

            if (!$edits_disabled || $is_admin)
            {
                $menuitems[]        = array('href' => get_permalink($report, 'edit'),
                                            'rel' => 'nofollow',
                                            'text' => 'Edit');
                if ($report->draft)
                {
                    $menuitems[]    = array('href' => 'javascript:void(0);',
                                            'onclick' => 'confirm_publish(\''.get_permalink($report, 'publish').'\');',
                                            'rel' => 'nofollow',
                                            'text' => 'Publish');
                }

                if ($is_admin)
                {
                    if (!$report->draft)
                    {
                        $menuitems[]    = array('href' => 'javascript:void(0);',
                                            'onclick' => 'confirm_unpublish(\''.get_permalink($report, 'unpublish').'\');',
                                            'rel' => 'nofollow',
                                            'text' => 'Unpublish');
                    }

                    if (!$report->deleted)
                    {
                        $menuitems[]    = array('href' => 'javascript:void(0);',
                                                'onclick' => 'confirm_delete(\''.get_permalink($report, 'delete').'\');',
                                                'rel' => 'nofollow',
                                                'text' => 'Delete');
                    }
                    else
                    {
                        $menuitems[]        = array('href' => get_permalink($report, 'undelete'),
                                                    'rel' => 'nofollow',
                                                    'text' => 'Undelete');
                    }
                }
            }
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

            echo '<div class="command_menu nonprinting">[ '.$menu_html.']</div>';
        }
    }


    /**
     * Show the detail view for the given report.
     *
     * @param Report $report                The given report.
     * @param string $link_url              The URL of the link to the report, if any.
     * @param boolean $show_map             true if a map should be shown; false otherwise.
     */
    function show_report($report, $link_url = '', $show_map = false)
    {
        $is_admin           = is_admin_user();

        $site_config        = get_config();
        $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];

        $heading            = $report->name;

        if ($report->draft)
        {
            $heading .= ' [Draft]';
        }

        if ($report->deleted)
        {
            $heading .= ' [Deleted]';
        }

        if ($link_url !== '')
        {
            $heading = "<a href='$link_url'>$heading</a>";
        }

        if (is_editor_user() && (!$edits_disabled || $is_admin) )
        {
            $menuitems[] = array('href' => get_permalink($report, 'edit'),
                                 'rel' => 'nofollow',
                                 'text' => 'Edit');

            if ($report->draft)
            {
                $menuitems[] = array('href' => 'javascript:void(0);',
                                     'onclick' => 'confirm_publish(\''.get_permalink($report, 'publish').'\');',
                                     'rel' => 'nofollow',
                                     'text' => 'Publish');
            }

            if ($is_admin)
            {
                if (!$report->draft)
                {
                    $menuitems[] = array('href' => 'javascript:void(0);',
                                         'onclick' => 'confirm_unpublish(\''.get_permalink($report, 'unpublish').'\');',
                                         'rel' => 'nofollow',
                                         'text' => 'Unpublish');
                }

                if (!$report->deleted)
                {
                    $menuitems[] = array('href' => 'javascript:void(0);',
                                         'onclick' => 'confirm_report_delete(\''.get_permalink($report, 'delete').'\');',
                                         'rel' => 'nofollow',
                                         'text' => 'Delete');
                }
                else
                {
                    $menuitems[] = array('href' => get_permalink($report, 'undelete'),
                                         'rel' => 'nofollow',
                                         'text' => 'Undelete');
                }
            }

            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).' | ';
            }

            // Trim trailing delimiter
            $menu_html = substr($menu_html, 0, strlen($menu_html) - 2);

            $heading .= '<span class="command_menu_inline nonprinting">&nbsp;&nbsp;[ '.$menu_html.']</span>';
        }

        $heading = "<h2>$heading</h2>";

        $summary = $heading;

        if ($report->age !== '')
        {
            $summary .= "Age $report->age";

            if (!empty($report->birthdate) )
            {
                $birthdate = date_str_to_display_date($report->birthdate);

                $summary .= " (born $birthdate)";
            }

            $summary .= '<br>';
        }

        $summary .= '<br>';

        $display_location   = htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8');

        if ($report->has_location() )
        {
            $display_location   = htmlspecialchars($report->location, ENT_QUOTES, 'UTF-8');

            $display_location .= ' ('.htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8').')';
        }

        $summary .= date_str_to_display_date($report->date).'<br>'.
                    $display_location.'<br>';

        $summary .= ucfirst($report->cause).'<br>';

        if ($report->tdor_list_ref !== '')
        {
            $summary .= "<br>TDoR list ref: $report->tdor_list_ref<br>";
        }

        echo "<br><p>$summary</p>";

        $photo_pathname = get_photo_pathname($report->photo_filename);
        $photo_caption  = '';

        if ($report->photo_filename !== '')
        {
            $photo_caption  = str_replace('"', '&quot;', $report->name);

            if ($report->photo_source !== '')
            {
                $photo_source_text = get_photo_source_text($report->photo_source);

                $photo_caption .= " [photo: $photo_source_text]";
            }
        }

        // Dispay the photo and caption
        $lightbox_caption = str_replace('"', "'", $photo_caption);
        $img_caption      = str_replace('"', '&quot;', $report->name);

        echo '<figure>';
        echo   '<a href="'.$photo_pathname.'" rel="lightbox" title="'.$lightbox_caption.'">';
        echo     '<img src="'.$photo_pathname.'" alt="'.$img_caption.'" />';
        echo   '</a>';
        echo   "<figcaption>$photo_caption<figcaption>";
        echo '</figure>';

        $desc = markdown_to_html($report->description);

        echo '<br>'.$desc;

        if ($show_map)
        {
            show_osm_map(array($report) );
        }

        // Add the date added/updated
        echo '<p class="report_timestamp">Report added: '.date_str_to_display_date($report->date_created, $is_admin);

        if ($report->date_updated > $report->date_created)
        {
            echo '. Last updated: '.date_str_to_display_date($report->date_updated, $is_admin);
        }

        echo '</p>';

        // QR code popup
        $qrcode_id          = "qrcode_$report->uid";
        $qrcode_pathname    = get_qrcode_pathname($report->uid);

        echo "<div class='qrcode_popup' id='$qrcode_id'>";
        echo '  <div class="qrcode_inner">';
        echo "    <div><img src='$qrcode_pathname' /></div>";
        echo '    <p align="center"><br><a href="javascript:hide_id(\''.$qrcode_id.'\')" >Close</a></p>';
        echo '  </div>';
        echo '</div>';

        show_menu_links_for_report($report);
        show_social_links_for_report($report);
    }


    /**
     * Show the detail view for the given reports.
     *
     * @param array $reports                An array of reports.
     *
     */
    function show_details($reports)
    {
        echo '<div>';

        foreach ($reports as $report)
        {
            show_report($report, get_permalink($report) );
            echo '<hr>';
        }

        echo '</div>';
    }

?>