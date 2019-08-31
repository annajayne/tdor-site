<?php
     /**
     * Report view implementation.
     *
     */


    /**
     * Show an item in the table for the given report.
     *
     * @param Report $report                The report to display details for.
     * @param string $photo_thumbnail       The filename of the photo thumbnail.
     * @param int $width                    The width of the photo thumbnail.
     * @param int $height                   The height of the photo thumbnail.
     */
    function show_summary_table_item($report, $photo_thumbnail, $width, $height)
    {
        $truncate_desc_to   = 100;
        $truncated_desc     = (strlen($report->description) > $truncate_desc_to) ? substr($report->description, 0, $truncate_desc_to).'...' : $report->description;

        $place              = $report->has_location() ? $report->location : '-';

        $link_url           = get_permalink($report);

        //$img_tag = '';

        //if ( ($width > 0) &&  ($height > 0) )
        //{
        //    $img_tag = "<a href='".$link_url."'><img src='".$photo_thumbnail."' alt='".$report->name."' width='".$width."' height='".$height."' /></a>";
        //}

        echo "<tr>";

        $name =$report->name;
        if ($report->deleted)
        {
            $name .= ' [Deleted]';
        }

        echo "<td style='white-space: nowrap;' sorttable_customkey='$report->date'>". get_display_date($report)."</td>";
        echo "<td><a href='".$link_url."'>".$name."</a></td>";
        echo "<td align='center'>". $report->age."</td>";
        //echo "<td>". $img_tag."</td>";
        //echo "<td>". $report->source_ref."</td>";


        echo "<td>". htmlspecialchars($place, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>". htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>". $report->cause."</td>";
       // echo "<td>". $truncated_desc."</td>";

        if (is_editor_user() )
        {
            $menuitems[] = array('href' => get_permalink($report, 'edit'),
                        'rel' => 'nofollow',
                        'text' => 'Edit');

            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).' ';
            }

            echo '<td align="center">'.$menu_html.'</td>';
        }

        echo "</tr>";
    }


    /**
     * Show the header row of the table for the given reports.
     *
     */
    function show_summary_table_header()
    {
        //$columns = array('Date', 'Name', 'Age', 'Photo', 'Location', 'Country', 'Cause');
        $columns = array('Date', 'Name', 'Age', 'Location', 'Country', 'Cause');

        if (is_logged_in() )
        {
            $columns[] = 'Action';
        }

        $headings = '';

        foreach ($columns as $column)
        {
            $align ='left';

            switch ($column)
            {
                case 'Age':
                //case 'Photo':
                    $align='center';
                    break;
            }

            $headings .= '<th align="'.$align.'">'.$column.'</th>';
        }

        echo '<tr>'.$headings.'</tr>';
    }


    /**
     * Show a table of the given reports.
     *
     * @param array $reports                An array containing the given reports.
     *
     */
    function show_summary_table($reports)
    {
        echo '<div class="grid12"><div class="reports_table">';
        echo '<table class="sortable">';

        show_summary_table_header();

        //$thumbnail_width_pixels = 150;

        foreach ($reports as $report)
        {
            $photo_pathname = '';
            $width = 0;
            $height = 0;

            //if ($report->photo_filename !== '')
            //{
            //    // Work out the size of the image
            //    $photo_pathname = "/data/photos/$report->photo_filename";

            //    $photo_size = get_image_size($photo_pathname);

            //    if (!empty($photo_size) )
            //    {
            //        $width      = $photo_size[0];
            //        $height     = $photo_size[1];

            //        if ( ($width > 0) &&  ($height > 0) && ($width > $thumbnail_width_pixels) )
            //        {
            //            $height = $height / ($width / $thumbnail_width_pixels);
            //            $width = $thumbnail_width_pixels;
            //        }
            //    }
            //}

            show_summary_table_item($report, $photo_pathname, $width, $height);
        }

        echo '</table></div></div>';
    }


?>
