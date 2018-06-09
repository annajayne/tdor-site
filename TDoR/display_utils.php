<?php

    // Equivalent for imagescale() for PHP versions which don't have it.
    //
    function imagescale_legacy_compat($source_image, $new_width, $new_height)
    {
        $dest_image = imagecreatetruecolor($new_width, $new_height);

        imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, imagesx($source_image), imagesy($source_image) );

        return $dest_image;
    }


    function create_overlay_image($output_pathname, $photo_pathname, $background_image_pathname)
    {
        $result                     = false;
        $root                       = $_SERVER['DOCUMENT_ROOT'];

        $background_image_size      = get_image_size($background_image_pathname);
        $photo_image_size           = get_image_size($photo_pathname);

        $background_image_aspect    = ($background_image_size[0] / $background_image_size[1]);
        $photo_image_aspect         = ($photo_image_size[0] / $photo_image_size[1]);

        if ($background_image_aspect !== $photo_image_aspect)
        {
            // Photo and background are different aspect ratios - create composite image with frame around photo
            $photo_scale_factor     = min( ($background_image_size[0] / $photo_image_size[0]), ($background_image_size[1] / $photo_image_size[1]) ) * 0.95;

            $main_image             = imagecreatefromjpeg($root.'/'.$background_image_pathname);
            $photo_image            = imagecreatefromjpeg($root.'/'.$photo_pathname);

            $new_width              = $photo_scale_factor * $photo_image_size[0];
            $new_height             = $photo_scale_factor * $photo_image_size[1];

            $photo_image            = imagescale_legacy_compat($photo_image, $new_width, $new_height);

            // Draw a white 5 pixel wide frame around the photo
            imagesetthickness($photo_image, 5);
            imagerectangle($photo_image, 0, 0, $new_width, $new_height, imagecolorallocate($photo_image, 255, 255, 255) );

            // Merge the photo onto the background with an opacity of 80%
            $dest_x                 = $background_image_size[0]/2 - ($new_width / 2);
            $dest_y                 =  $background_image_size[1]/2 - ($new_height / 2);

            imagecopymerge($main_image, $photo_image, $dest_x, $dest_y, 0, 0, imagesx($photo_image), imagesy($photo_image), 90);

            // Save the image to file and free memory
            $result = imagejpeg($main_image, $output_pathname);

            imagedestroy($main_image);
            imagedestroy($photo_image);
        }
        else
        {
            // Photo and background have the same aspect ratio - just copy the photo to the output file
            $result = copy($photo_pathname, $output_pathname);
        }
        return $result;
    }


    function replace_accents($str)
    {
        $str = htmlentities($str);
        $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|elig|ring|th|slash|zlig|horn);/','$1',$str);

        return html_entity_decode($str);
    }


    function date_str_to_iso($date_str)
    {
        $date_components    = date_parse($date_str);

        $day                = $date_components['day'];
        $month              = $date_components['month'];
        $year               = $date_components['year'];

        $date_iso           = strval($year).'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day);

        return $date_iso;
    }


    function date_str_to_display_date($date_str)
    {
        $date = new DateTime($date_str);

        return $date->format('d M Y');
    }


    function get_tdor_year($date)
    {
        $year   = $date->format("Y");
        $month  = $date->format("m");

        if ($month >= 10)
        {
            ++$year;
        }
        return $year;
    }


    function get_display_date($report)
    {
        return date_str_to_display_date($report->date);
    }


    function get_displayed_cause_of_death($report)
    {
        $cause = '';

        if (strpos($report->cause, 'custody') !== false)
        {
            $cause = "died in custody";
        }
        else if (strpos($report->cause, 'throat cut') !== false)
        {
            $cause = "was stabbed";
        }
        else if ( (strpos($report->cause, 'suicide') !== false) ||
             (strpos($report->cause, 'malpractice') !== false) ||
             (strpos($report->cause, 'silicone') !== false) )
        {
            $cause = "died by $report->cause";
        }
        else if ($report->cause !== 'not reported')
        {
            $cause = "was $report->cause";
        }
        else
        {
            $cause = 'died';
        }
        return $cause;
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


    function get_image_size($filename)
    {
        $photo_size = array();

        if ($filename !== '')
        {
            $root = $_SERVER['DOCUMENT_ROOT'];

            // Work out the size of the image
            if (file_exists($root.$filename) )
            {
                $photo_size = getimagesize($root.$filename);
            }
        }
        return $photo_size;
    }


    function get_photo_pathname($filename = '')
    {
        $pathname = '/images/victim_default_photo.jpg';

        if ($filename !== '')
        {
            $pathname = "/data/photos/$filename";
        }
        return $pathname;
    }


    function get_friendly_link($report)
    {
        $date = new DateTime($report->date);

        $hyphen = '-';
        $underscore = '_';

        $main_field = strtolower(replace_accents($report->name.$underscore.$report->location.$underscore.$report->country) );

        $main_field = str_replace(' ',  $hyphen,        $main_field);
        $main_field = preg_replace("/[^a-zA-Z_-]/", "", $main_field);

        $main_field = urlencode($main_field);                               // Just in case we missed anything...

        $permalink = '/reports/'.$date->format('Y/m/d').'/'.$main_field.'-'.$report->uid;

        return $permalink;
    }


    function get_permalink($report, $action= 'show')
    {
        if (ENABLE_FRIENDLY_URLS)
        {
            return get_friendly_link($report);
        }

        // Raw urls
        $url = "/index.php?category=reports&action=$action";

        if (!empty($report->uid) )
        {
            $url .= '&uid='.$report->uid;
        }
        else
        {
            $url .= '&id='.$report->id;
        }
        return $url;
    }

    function get_summary_text($report)
    {
        $date       = get_display_date($report);
        $location   = "$report->location, $report->country";
        $desc       = $report->name;

        if ($report->age !== '')
        {
            $desc .= " was $report->age and";
        }

        $desc      .= ' '.get_displayed_cause_of_death($report);
        $desc      .= " in $location";

        $title      = "$report->name ($date)";

        return array('title' => $title,
                     'desc' => $desc,
                     'date' => $date,
                     'location' => $location);
    }


    function show_social_links($url, $text = "")
    {
        $encoded_url    = rawurlencode($url);

        if (empty($text) )
        {
            $text = $url;
        }

        echo '<div id="social_links">';
        echo   "<a href='https://www.facebook.com/sharer/sharer.php?u=$encoded_url' title='Share on Facebook' target='_blank'><img src='/images/social/facebook.svg' /></a>";
        echo   "<a href='https://twitter.com/home?status=$text' title='Tweet about this' target='_blank'><img src='/images/social/twitter.svg' /></a>";
        echo '</div>';
    }


    function show_social_links_for_report($report)
    {
        $url            = get_host().get_permalink($report);

        $summary_text   = get_summary_text($report);
        $newline        ='%0A';

        $tweet_text     = htmlspecialchars($summary_text['desc'], ENT_QUOTES).' ('.$summary_text['date'].').'.$newline.$newline.rawurlencode($url);

        show_social_links($url, $tweet_text);
    }

?>