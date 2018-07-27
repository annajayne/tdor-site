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
            if ($photo_image === false)
            {
                $photo_image        = imagecreatefrompng($root.'/'.$photo_pathname);
            }

            if ($photo_image !== false)
            {
                $new_width          = $photo_scale_factor * $photo_image_size[0];
                $new_height         = $photo_scale_factor * $photo_image_size[1];

                $photo_image        = imagescale_legacy_compat($photo_image, $new_width, $new_height);

                // Draw a white 5 pixel wide frame around the photo
                imagesetthickness($photo_image, 5);
                imagerectangle($photo_image, 0, 0, $new_width, $new_height, imagecolorallocate($photo_image, 255, 255, 255) );

                // Merge the photo onto the background with an opacity of 80%
                $dest_x             = $background_image_size[0]/2 - ($new_width / 2);
                $dest_y             = $background_image_size[1]/2 - ($new_height / 2);

                imagecopymerge($main_image, $photo_image, $dest_x, $dest_y, 0, 0, imagesx($photo_image), imagesy($photo_image), 90);

                // Save the image to file and free memory
                $result = imagejpeg($main_image, $output_pathname);

                imagedestroy($main_image);
                imagedestroy($photo_image);
            }
        }
        else
        {
            // Photo and background have the same aspect ratio - just copy the photo to the output file
            $result = copy($root.'/'.$photo_pathname, $output_pathname);
        }
        return $result;
    }


    function replace_accents($str)
    {
        $str = htmlentities($str);
        $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|elig|ring|th|slash|zlig|horn);/','$1',$str);

        return html_entity_decode($str);
    }


    function make_iso_date($year, $month, $day)
    {
        return strval($year).'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day);
    }


    function date_str_to_iso($date_str)
    {
        $date_components    = date_parse($date_str);

        $day                = $date_components['day'];
        $month              = $date_components['month'];
        $year               = $date_components['year'];

        return make_iso_date($year, $month, $day);
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
             (strpos($report->cause, 'silicone') !== false) ||
             (strpos($report->cause, 'clinical') !== false) )
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


    function generate_photo_filename($report, $extension)
    {
        $date_components    = date_parse($report->date);

        $day                = $date_components['day'];
        $month              = $date_components['month'];
        $year               = $date_components['year'];

        $name               = replace_accents($report->name);
        $name               = str_replace(' ', '-', $name);


        $underscore         = '_';

        $filename   = strval($year).$underscore.sprintf('%02d', $month).$underscore.sprintf('%02d', $day).$underscore.$name.'_'.$report->uid.'.'.$extension;

        return $filename;
    }


    function get_slider_main_caption()
    {
        $year       = date('Y');
        $month_day  = date('m-d');

        $verb       = ($month_day <= '11-20') ? 'is' : 'was';

        $caption    = "Transgender Day of Remembrance $verb <b>20th November $year</b>.";

        return $caption;
    }


    function is_photo_upload_valid($file)
    {
        if (empty($file["name"]) )
        {
            return false;
        }

        $ok = true;

        $check = getimagesize($file["tmp_name"]);
        if ($check === false)
        {
            $error  = "File is not an image.";
            $ok     = false;
        }

        if ($file["size"] > 10485760)
        {
            $error  = 'File too large (max 10MB)';
            $ok     = false;
        }

        //if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg")
        //{
        //    $error = "Sorry, only JPG, JPEG & PNG files are allowed.";
        //    $ok = false;
        //}
        return $ok;
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


    function create_photo_thumbnail($photo_filename, $replace_if_exists = false)
    {
        $root = $_SERVER['DOCUMENT_ROOT'];

        $default_image_filename = get_photo_pathname();

        $folder = "$root/data/thumbnails";

        $thumbnail_pathname = "$folder/$photo_filename";
        $photo_pathname     = !empty($photo_filename) ? "$root/data/photos/$photo_filename" : '';

        if (file_exists($thumbnail_pathname) && $replace_if_exists)
        {
            unlink($thumbnail_pathname);
        }

        if (!file_exists($thumbnail_pathname) )
        {
            if (!create_overlay_image($thumbnail_pathname, get_photo_pathname($photo_filename), $default_image_filename) )
            {
                echo "  ERROR: Thumbnail image $photo_filename NOT created";

                if (!empty($photo_pathname) && !file_exists($photo_pathname) )
                {
                    echo " (file $photo_filename not found)";
                }

                echo '<br>';
            }
        }
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


    function get_date_range_from_year_month_day($year, $month, $day)
    {
        $date_from_str = '';
        $date_to_str = '';

        if ($year > 0)
        {
            if ($month > 0)
            {
                if ($day > 0)
                {
                    $date_from_str  = make_iso_date($year, $month, $day);
                    $date_to_str    = make_iso_date($year, $month, $day);
                }
                else
                {
                    $date_from_str  = make_iso_date($year, $month, 1);
                    $date_to_str    = make_iso_date($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year) );
                }
            }
            else
            {
                $date_from_str      = make_iso_date($year, 1, 1);
                $date_to_str        = make_iso_date($year, 12, 31);
            }
        }
        return array($date_from_str, $date_to_str);
    }


    function get_date_range_from_url($path)
    {
        $range = array();

        if (ENABLE_FRIENDLY_URLS)
        {
            $elements = explode('/', $path);                // Split path on slashes

            // e.g. tdor.translivesmatter.info/reports/year/month/day/name
            $element_count = count($elements);

            if ( ($element_count >= 1) && ($elements[0] == 'reports') )
            {
                $year       = 0;
                $month      = 0;
                $day        = 0;

                if ($element_count >= 2)
                {
                    $year = intval($elements[1]);
                }
                if ($element_count >= 3)
                {
                    $month = intval($elements[2]);
                }
                if ($element_count >= 4)
                {
                    $day = intval($elements[3]);
                }

                $range = get_date_range_from_year_month_day($year, $month, $day);
            }
        }
        return $range;
    }


    function get_friendly_link($report, $action)
    {
        $date = new DateTime($report->date);

        $hyphen = '-';
        $underscore = '_';

        $main_field = strtolower(replace_accents($report->name.$underscore.$report->location.$hyphen.$report->country) );

        $main_field = str_replace(' ',  $hyphen,        $main_field);
        $main_field = preg_replace("/[^a-zA-Z_-]/", "", $main_field);

        $main_field = urlencode($main_field);                               // Just in case we missed anything...

        $permalink = '/reports/'.$date->format('Y/m/d').'/'.$main_field.$underscore.$report->uid;

        if ($action != 'show')
        {
            $permalink .= "?action=$action";
        }
        return $permalink;
    }


    function get_permalink($report, $action= 'show')
    {
        if (ENABLE_FRIENDLY_URLS)
        {
            return get_friendly_link($report, $action);
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


    function get_uid_from_friendly_url($url)
    {
        $elements = explode('/', $url);                // Split path on slashes

        // e.g. tdor.annasplace.me.uk/reports/year/month/day/name
        $element_count = count($elements);

        if ( ($element_count == 5) && ($elements[0] == 'reports') )
        {
            $year       = $elements[1];
            $month      = $elements[2];
            $day        = $elements[3];

            $name       = urldecode($elements[4]);

            $query_pos = strpos($name, '?');

            if ($query_pos > 0)
            {
                // Strip off the queries
                $name = substr($name, 0, $query_pos);
            }

            $name_len   = strlen($name);

            $uid_len = 8;
            $uid_delimiter_pos = $name_len - ($uid_len + 1);

            if ( ($name_len > $uid_len) && ( ($name[$uid_delimiter_pos] === '-') || ($name[$uid_delimiter_pos] === '_') ) )
            {
                $uid = substr($name, -$uid_len);

                // Validate
                if (is_valid_hex_string($uid) )
                {
                    return $uid;
                }
            }
        }
        return '';
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


    function get_outa_here_url()
    {
        $urls = array('https://www.youtube.com/watch?v=kMhw5MFYU0s',            // Dogs who fail at being dogs
                      'https://twitter.com/search?q=%23TransIsBeautiful',       // #TransIsBeautiful
                      'https://www.youtube.com/watch?v=Of2HU3LGdbo',            // Cat In A Shark Costume Chases A Duck While Riding A Roomba
                      'https://twitter.com/search?q=%23TransOnTrains',          // #TransOnTrains
                      'https://www.youtube.com/watch?v=MujRLvZ61jE');           // WOOF!

        $n = rand(0, count($urls) - 1);

        return $urls[$n];
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