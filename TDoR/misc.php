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


    function get_display_date($item)
    {
        $date = new DateTime($item->date);

        return $date->format('d M Y');
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



?>