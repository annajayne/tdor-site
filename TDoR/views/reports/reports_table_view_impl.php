<?php
    function show_summary_table_item($report, $photo_thumbnail, $width, $height)
    {
        $truncate_desc_to = 100;

        $truncated_desc = (strlen($report->description) > $truncate_desc_to) ? substr($report->description, 0, $truncate_desc_to).'...' : $report->description;

        $link_url = get_permalink($report);

        $img_tag = '';

        if ( ($width > 0) &&  ($height > 0) )
        {
            $img_tag = "<a href='".$link_url."'><img src='".$photo_thumbnail."' alt='".$report->name."' width='".$width."' height='".$height."' /></a>";
        }

        echo "<tr>";

        echo "<td style='white-space: nowrap;' sorttable_customkey='$report->date'>". get_display_date($report)."</td>";
        echo "<td><a href='".$link_url."'>".$report->name."</a></td>";
        echo "<td align='center'>". $report->age."</td>";
        echo "<td>". $img_tag."</td>";
        //echo "<td>". $report->tgeu_ref."</td>";
        echo "<td>". $report->location."</td>";
        echo "<td>". $report->country."</td>";
        echo "<td>". $report->cause."</td>";
       // echo "<td>". $truncated_desc."</td>";

        echo "</tr>";
    }


    function show_summary_table_header()
    {
        $columns = array('Date', 'Name', 'Age', 'Photo', 'Location', 'Country', 'Cause');

        $headings = '';

        foreach ($columns as $column)
        {
            $align ='left';

            switch ($column)
            {
                case 'Age':
                case 'Photo':
                    $align='center';
                    break;
            }

            $headings .= '<th align="'.$align.'">'.$column.'</th>';
        }

        echo '<tr>'.$headings.'</tr>';
    }


    function show_summary_table($reports)
    {
        echo '<div class="grid12"><div class="reports_table">';
        echo '<table class="sortable">';

        show_summary_table_header();

        $thumbnail_width_pixels = 150;

        foreach ($reports as $report)
        {
            $photo_pathname = '';
            $width = 0;
            $height = 0;

            if ($report->photo_filename !== '')
            {
                // Work out the size of the image
                $photo_pathname = "/data/photos/$report->photo_filename";

                $photo_size = get_image_size($photo_pathname);

                if (!empty($photo_size) )
                {
                    $width      = $photo_size[0];
                    $height     = $photo_size[1];

                    if ( ($width > 0) &&  ($height > 0) && ($width > $thumbnail_width_pixels) )
                    {
                        $height = $height / ($width / $thumbnail_width_pixels);
                        $width = $thumbnail_width_pixels;
                    }
                }
            }

            show_summary_table_item($report, $photo_pathname, $width, $height);
        }

        echo '</table></div></div>';
    }


?>
