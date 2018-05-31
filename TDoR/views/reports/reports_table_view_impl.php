<?php
    function show_summary_table_item($item, $photo_thumbnail, $width, $height)
    {
        $truncate_desc_to = 100;

        $truncated_desc = (strlen($item->description) > $truncate_desc_to) ? substr($item->description, 0, $truncate_desc_to).'...' : $item->description;

        $link_url = get_permalink($item);

        $img_tag = '';

        if ( ($width > 0) &&  ($height > 0) )
        {
            $img_tag = "<a href='".$link_url."'><img src='".$photo_thumbnail."' alt='".$item->name."' width='".$width."' height='".$height."' /></a>";
        }

        echo "<tr>";

        echo "<td style='white-space: nowrap;' sorttable_customkey='$item->date'>". get_display_date($item)."</td>";
        echo "<td><a href='".$link_url."'>".$item->name."</a></td>";
        echo "<td align='center'>". $item->age."</td>";
        echo "<td>". $img_tag."</td>";
        //echo "<td>". $item->tgeu_ref."</td>";
        echo "<td>". $item->location."</td>";
        echo "<td>". $item->country."</td>";
        echo "<td>". $item->cause."</td>";
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

        foreach ($reports as $item)
        {
            $photo_pathname = '';
            $width = 0;
            $height = 0;

            if ($item->photo_filename !== '')
            {
                // Work out the size of the image
                $photo_pathname = "/data/photos/$item->photo_filename";

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

            show_summary_table_item($item, $photo_pathname, $width, $height);
        }

        echo '</table></div></div>';
    }


?>
