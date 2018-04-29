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



?>