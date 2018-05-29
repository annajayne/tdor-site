<?php

    function show_report($item, $link_url = '')
    {
        $heading = "<h2>$item->name</h2>";

        if ($link_url !== '')
        {
            $heading = "<a href='$link_url'>$heading</a>";
        }

        $summary = $heading;

        if ($item->age !== '')
        {
            $summary .= "Age $item->age<br>";
        }

        $display_location = $item->location;

        if ($item->country !== '')
        {
            $display_location .= " ($item->country)";
        }

        $summary .= get_display_date($item).'<br>'.
                    $display_location.'<br>';

        if ($item->tgeu_ref !== '')
        {
            $summary .= "TGEU ref: $item->tgeu_ref<br>";
        }

        $summary .= ucfirst($item->cause).'<br>';

        echo "<br><p>$summary</p>";

        $photo_pathname = get_photo_pathname($item->photo_filename);
        $photo_caption  = '';

        if ($item->photo_filename !== '')
        {
            $photo_caption  = $item->name;

            if ($item->photo_source !== '')
            {
                $photo_source_text = get_photo_source_text($item->photo_source);

                $photo_caption .= (" [photo: $photo_source_text]");
            }
        }

        // Dispay the photo and caption
        echo "<div class='photo_caption''>";
        echo   "<img src='".$photo_pathname."' alt='".$item->name."' /><br>";
        echo   $photo_caption.'<br>';
        echo "</div>";

        // Convert line breaks to paragraphs and expand any links
        $desc = markdown_to_html($item->description);
        $desc = linkify($desc, array('http', 'mail'), array('target' => '_blank') );

        echo "<br>$desc<br><br>";
    }


    function show_details($items)
    {
        foreach ($items as $item)
        {
            show_report($item, get_item_url($item) );
            echo '<hr>';
        }
    }

?>