<?php


    function log_text($text)
    {
      //  echo $text."<br>";
    }


    function log_error($text)
    {
        echo $text."<br>";
    }


    function str_begins_with($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }


    function str_ends_with($haystack, $needle)
    {
        return strrpos($haystack, $needle) + strlen($needle) === strlen($haystack);
    }


    function is_valid_hex_string($value)
    {
        return (dechex(hexdec($value) ) === $value);
    }


    function get_random_hex_string($num_bytes = 4)
    {
        return bin2hex(openssl_random_pseudo_bytes($num_bytes) );
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


    function replace_accents($str)
    {
        $str = htmlentities($str);
        $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|elig|ring|th|slash|zlig|horn);/','$1',$str);
        return html_entity_decode($str);
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


    function get_permalink($item)
    {
        $date = new DateTime($item->date);

        $hyphen = '-';
        $underscore = '_';

        $main_field = strtolower(replace_accents($item->name.$underscore.$item->location.$underscore.$item->country) );

        $main_field = str_replace(' ',  $hyphen,        $main_field);
        $main_field = preg_replace("/[^a-zA-Z_-]/", "", $main_field);

        $main_field = urlencode($main_field);                               // Just in case we missed anything...

        $permalink = '/reports/'.$date->format('Y/m/d').'/'.$main_field.'-'.$item->uid;

        return $permalink;
    }


    function get_item_url($item)
    {
        if (ENABLE_FRIENDLY_URLS)
        {
            return get_permalink($item);
        }

        // Raw urls
        $url = '/index.php?category=reports&action=show';

        if (!empty($item->uid) )
        {
            $url .= '&uid='.$item->uid;
        }
        else
        {
            $url .= '&id='.$item->id;
        }
        return $url;
    }


    function get_display_date($item)
    {
        $date = new DateTime($item->date);

        return $date->format('d M Y');
    }


    function get_displayed_cause_of_death($item)
    {
        $cause = '';

        if (strpos($item->cause, 'custody') !== false)
        {
            $cause = "died in custody";
        }
        else if ( (strpos($item->cause, 'suicide') !== false) ||
             (strpos($item->cause, 'malpractice') !== false) ||
             (strpos($item->cause, 'silicone') !== false) )
        {
            $cause = "died by $item->cause";
        }
        else if ($item->cause !== 'not reported')
        {
            $cause = "was $item->cause";
        }
        else
        {
            $cause = 'died';
        }
        return $cause;
    }


    function get_tdor_year($date)
    {
        $year = $date->format("Y");
        $month = $date->format("m");

        if ($month >= 10)
        {
            ++$year;
        }
        return $year;
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


?>