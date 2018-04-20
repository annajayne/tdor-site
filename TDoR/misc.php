<?php


    function log_text($text)
    {
      //  echo $text."<br>";
    }


    function log_error($text)
    {
        echo $text."<br>";
    }


    function date_str_to_utc($date_str)
        {
        $date_components    = date_parse($date_str);

        $day                = $date_components['day'];
        $month              = $date_components['month'];
        $year               = $date_components['year'];

        $date_utc           = strval($year).'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day);

        return $date_utc;
        }


    function get_display_date($item)
    {
        $date = new DateTime($item->date);

        return $date->format('d M Y');
    }


    function get_photo_source_text($photo_source)
    {
        $protocol_http  = 'http://';
        $protocol_https = 'https://';

        if ( ($protocol_http == substr($photo_source, 0, strlen($protocol_http) ) ) ||
             ($protocol_https == substr($photo_source, 0, strlen($protocol_https) ) ) )
        {
            // This is a link
            return "<a href='$photo_source' target='_blank'>".parse_url($photo_source, PHP_URL_HOST)."</a>";
        }
        return $photo_source;
    }


    function show_post($post, $link_url = '')
    {
        $heading = "<h2>$post->name</h2>";

        if ($link_url !== '')
        {
            $heading = "<a href='$link_url'>$heading</a>";
        }

        $summary = $heading;

        if ($post->age !== '')
        {
            $summary .= "Age $post->age<br>";
        }

        $display_location = $post->location;

        if ($post->country !== '')
        {
            $display_location .= " ($post->country)";
        }

        $summary .= get_display_date($post).'<br>'.
                    $display_location.'<br>';

        if ($post->tgeu_ref !== '')
        {
            $summary .= "TGEU ref: $post->tgeu_ref<br>";
        }

        $summary .= ucfirst($post->cause).'<br>';

        echo "<br><p>$summary</p>";

        $photo_pathname = 'images/victim_default_photo.jpg';
        $photo_caption  = '';

        if ($post->photo_filename !== '')
        {
            $photo_pathname = "data/photos/$post->photo_filename";
            $photo_caption  = $post->name;

            if ($post->photo_source !== '')
            {
                $photo_source_text = get_photo_source_text($post->photo_source);

                $photo_caption .= (" [photo: $photo_source_text]");
            }
        }

        // Dispay the photo and caption
        echo "<div class='photo_caption''>";
        echo   "<img src='".$photo_pathname."' alt='".$post->name."' /><br>";
        echo   $photo_caption.'<br>';
        echo "</div>";


        // Convert line breaks to paragraphs and expand any links
        $desc = nl2p2($post->description);
        $desc = linkify($desc, array('http', 'mail'), array('target' => '_blank') );

        echo "<br>$desc<br><br>";
    }

?>