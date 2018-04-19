<?php 
    function print_summary_table_header()
    {
        $columns = array('Date', 'Name', 'Age', 'Photo', 'Location', 'Country', 'Cause');

        $headings = '';

        foreach ($columns as $column)
        {
            $align='left';

            switch ($column)
            {
                case 'Age':
                case 'Photo':
                    $align='center';
                    break;
            }

            $headings .= '<th align="'.$align.'">'.$column.'</th>';
        }

        print '<tr>'.$headings.'</tr>';
    }

    function get_item_url($item)
    {
        return '?controller=posts&action=show&id='.$item->id;
    }


    function print_summary_table_item($item, $photo_thumbnail, $width, $height)
    {
        $truncate_desc_to = 100;

        $truncated_desc = (strlen($item->description) > $truncate_desc_to) ? substr($item->description, 0, $truncate_desc_to).'...' : $item->description;

        $link_url = get_item_url($item);

        $img_tag = '';

        if ( ($width > 0) &&  ($height > 0) )
        {
            $img_tag = "<a href='".$link_url."'><img src='".$photo_thumbnail."' alt='".$item->name."' width='".$width."' height='".$height."' /></a>";
        }

        print "<tr>";

        print "<td style='white-space: nowrap;'>". get_display_date($item)."</td>";
        print "<td><a href='".$link_url."'>".$item->name."</a></td>";
        print "<td align='center'>". $item->age."</td>";
        print "<td>". $img_tag."</td>";
        //print "<td>". $item->tgeu_ref."</td>";
        print "<td>". $item->location."</td>";
        print "<td>". $item->country."</td>";
        print "<td>". $item->cause."</td>";
       // print "<td>". $truncated_desc."</td>";

        print "</tr>";
    }


    function print_summary_table($posts)
    {
        print '<div class="grid12"><div style="overflow-x:auto;">';
        print '<table class="sortable">';
        print_summary_table_header();
  
        $thumbnail_width_pixels = 150;

        foreach ($posts as $post)
        {
            $photo_pathname = '';
            $width = 0;
            $height = 0;

            if ($post->photo_filename !== '')
            {
                // Work out the size of the image
                $photo_pathname = "data/photos/".$post->photo_filename;
                if (file_exists($photo_pathname) )
                {
                    $photo_size = getimagesize($photo_pathname);

                    $width      = $photo_size[0];
                    $height     = $photo_size[1];

                    if ( ($width > 0) &&  ($height > 0) && ($width > $thumbnail_width_pixels) )
                    {
                        $height = $height / ($width / $thumbnail_width_pixels);
                        $width = $thumbnail_width_pixels;
                    }
                }
            }

            print_summary_table_item($post, $photo_pathname, $width, $height);
        }
        print '</table></div></div>';

    }


    function print_details($items)
    {
        foreach ($items as $item)
        {
            show_post($item, get_item_url($item) );
            echo '<hr>';
        }
    }

    $post_count = count($posts);

    if ($post_count > 0)
    {


        print_summary_table($posts);
    }
    else
    {
        print '<br>No entries';
  }
?>