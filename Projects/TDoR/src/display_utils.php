<?php
    /**
     * Display utility functions.
     *
     */


    /**
     * Equivalent for imagescale() for PHP versions which don't have it.
     *
     * @param resource $source_image              The source image
     * @param int $new_width                      The width of the scaled image
     * @param int $new_height                     The height of the scaled image
     * @return resource                           The scaled image.
     */
    function imagescale_legacy_compat($source_image, $new_width, $new_height)
    {
        $dest_image = imagecreatetruecolor($new_width, $new_height);

        imagecopyresampled($dest_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, imagesx($source_image), imagesy($source_image) );

        return $dest_image;
    }


    /**
     * Create an overlay image.
     *
     * @param string $output_pathname             The pathname of the output image (PNG or JPG).
     * @param string $photo_pathname              The pathname of the photo to overlay over the background.
     * @param string $background_image_pathname   The patname of the background image, which also defined the image size.
     * @return boolean                            true if the composite image was created successfully; false otherwise.
     */
    function create_overlay_image($output_pathname, $photo_pathname, $background_image_pathname)
    {
        $result                     = false;
        $root                       = get_root_path();

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

                // Merge the photo onto the background with an opacity of 100%
                $dest_x             = $background_image_size[0]/2 - ($new_width / 2);
                $dest_y             = $background_image_size[1]/2 - ($new_height / 2);

                imagecopymerge($main_image, $photo_image, $dest_x, $dest_y, 0, 0, imagesx($photo_image), imagesy($photo_image), 100);

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


    /**
     * Create a QR code for the specified target.
     *
     * @param string $target                      The string to encode into the QR code.
     * @param string $pathname                    The pathname of the PNG file to save the QR code to.
     */
    function create_qrcode($target, $pathname)
    {
        QRcode::png($target, $pathname, 'L', 4, 2);
    }


    /**
     * Get the filename of the QR code for the specified report.
     *
     * @param Report $report                    The report corresponding to the QR code.
     * @return string                           The filename of the QR code image.
     */
    function get_qrcode_filename($report)
    {
        $filename   = "data/qrcodes/$report->uid.png";

        return $filename;
    }


    /**
     * Create a QR code for the specified target.
     *
     * @param Report $report                      The report for which the QR code should be generated.
     * @param boolean $update_existing            true if the existing (if any) QR code image file should be regenerated; false otherwise.
     * @return boolean                            true if the QR code was created; false otherwise.
     */
    function create_qrcode_for_report($report, $update_existing = true)
    {
        $root       = get_root_path();
        $target     = get_host().get_permalink($report);

        $filename   = get_qrcode_filename($report);
        $pathname   = "$root/$filename";

        $exists     = file_exists($pathname);

        if (!$exists || $update_existing)
        {
            if ($exists)
            {
                unlink($pathname);
            }
            return create_qrcode($target, $filename);
        }
        return false;
    }


    /**
     * Replace the accents in the given string with ANSI equivalents.
     *
     * @param string $str                         The source text.
     * @return string                             The source text, converted to ANSI.
     */
    function replace_accents($str)
    {
        // BODGE ALERT!!!
        //
        // The ghastliness with get_html_translation_table() and $new_entities below is only necessary because our web host uses PHP 5.3.
        // With PHP 5.4 (probably) or later all of the code below can be replaced with
        // $str = htmlentities($str);

        static $new_entities = array();

        if (empty($new_entities) )
        {
            $entities = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, 'UTF-8');
            foreach ($entities as $entity)
            {
                $new_entities[html_entity_decode($entity, ENT_QUOTES, 'UTF-8')] = $entity;
            }
        }

        $str = strtr($str, $new_entities);
        // END OF BODGE

        $str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|cedil|elig|ring|th|slash|zlig|horn);/','$1',$str);

        return html_entity_decode($str);
    }


    /**
     * Create an ISO date string fro the given year, month and day.
     *
     * @param int $year                           The year,
     * @param int $month                          The month.
     * @param int $day                            The day.
     * @return string                             The corresponding ISO date string.
     */
    function make_iso_date($year, $month, $day)
    {
        return strval($year).'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day);
    }


    /**
     * Convert the given date string to an ISO date representation.
     *
     * @param string $date_str                    The date to parse (e.g. "27 Jul 2018"),
     * @return string                             The corresponding ISO date string.
     */
    function date_str_to_iso($date_str)
    {
        $date_components    = date_parse($date_str);

        $day                = $date_components['day'];
        $month              = $date_components['month'];
        $year               = $date_components['year'];

        return make_iso_date($year, $month, $day);
    }


    /**
     * Convert the given date string to a display representation of the form "dd MMM YYYY".
     *
     * @param string $date_str                    The date to parse.
     * @return string                             The corresponding display date.
     */
    function date_str_to_display_date($date_str)
    {
        $date = new DateTime($date_str);

        return $date->format('j M Y');
    }


    /**
     * Determine which TDoR year the given date occurs within.
     *
     * @param DateTime $date                      The date to check.
     * @return int                                The corresponding TDoR year.
     */
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


    /**
     * Get the display date for the given report.
     *
     * @param Report $report                      The source report.
     * @return string                             The corresponding display date.
     */
    function get_display_date($report)
    {
        return date_str_to_display_date($report->date);
    }


    /**
     * Get the displayed cause of death (as used in the slideshow and thumbnail views) corresponding to the given report.
     *
     * @param Report $report                      The source report.
     * @return string                             The corresponding cause of death.
     */
    function get_displayed_cause_of_death($report)
    {
        $cause = 'died';

        if (stripos($report->cause, 'custody') !== false)
        {
            $cause = "died in custody";
        }
        else if (stripos($report->cause, 'throat cut') !== false)
        {
            $cause = "was stabbed";
        }
        else if (stripos($report->cause, 'fell') !== false)
        {
            $cause = "fell";
        }
        else if ( (stripos($report->cause, 'suicide') !== false) ||
                  (stripos($report->cause, 'malpractice') !== false) ||
                  (stripos($report->cause, 'silicone') !== false) ||
                  (stripos($report->cause, 'cosmetic') !== false) ||
                  (stripos($report->cause, 'clinical') !== false) )
        {
            $cause = "died by $report->cause";
        }
        else if (stripos($report->cause, 'medical') !== false)
        {
            $cause = "died from $report->cause";
        }
        else if (strtolower($report->cause) !== 'not reported')
        {
            $cause = "was $report->cause";
        }
        return $cause;
    }


    /**
     * Get the short description (as used in memorial cards) corresponding to the given report.
     *
     * @param Report $report                      The source report.
     * @return string                             The corresponding short description.
     */
    function get_short_description($report)
    {
        $desc = $report->description;

        $max_len = 180;

        $pos = strpos($desc, "\n");
        if ($pos > 0)
        {
            $desc = substr($desc, 0, strpos($desc, "\n") );
        }

        if (strlen($desc) > $max_len)
        {
            $temp = substr($desc, 0, $max_len);

            $desc = substr($temp, 0, strrpos($temp, ' ') ).'...';
        }
        return $desc;
    }


    /**
     * Generate a filename for the photo associated with the given report.
     *
     * @param Report $report                      The source report.
     * @param string $extension                   The extension of the photo filename.
     * @return string                             The generated filename, of the form "yyyy_MMM_dd_<Name>_<UID>.ext".
     */
    function generate_photo_filename($report, $extension)
    {
        $date_components    = date_parse($report->date);

        $day                = $date_components['day'];
        $month              = $date_components['month'];
        $year               = $date_components['year'];

        $name               = str_replace('(', '', trim($report->name) );
        $name               = str_replace(')', '', $name);

        $name               = replace_accents($name);
        $name               = str_replace(' ', '-', $name);

        $underscore         = '_';

        $filename   = strval($year).$underscore.sprintf('%02d', $month).$underscore.sprintf('%02d', $day).$underscore.$name.'_'.$report->uid.'.'.$extension;

        return $filename;
    }


    /**
     * Get the main caption for the slider, based on the current TDoR year.
     *
     * @return string                             The generated caption.
     */
    function get_slider_main_caption()
    {
        $year       = date('Y');
        $month_day  = date('m-d');

        $verb       = ($month_day <= '11-20') ? 'is' : 'was';

        $caption    = "Transgender Day of Remembrance $verb <b>20th November $year</b>.";

        return $caption;
    }


    /**
     * Determine if the specified photo upload is valid.
     *
     * @param array $file                         Details of the uploaded file.
     * @return boolean                            true if the file is valid; false otherwise.
     */
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


    /**
     * Generate a link for the source of a photo of a victim. The visible text is the hostname of the link rather than its full text.
     *
     * @param string $photo_source                The URL of the source.
     * @return string                             A link for the given URL.
     */
    function get_photo_source_text($photo_source)
    {
        $protocol_http  = 'http://';
        $protocol_https = 'https://';

        if ( ($protocol_http == substr($photo_source, 0, strlen($protocol_http) ) ) ||
             ($protocol_https == substr($photo_source, 0, strlen($protocol_https) ) ) )
        {
            // This is a link
            return "<a href='$photo_source' target='_blank' rel='noopener'>".parse_url($photo_source, PHP_URL_HOST)."</a>";
        }
        return $photo_source;
    }


    /**
     * Get the full path of the thumbnail image for the given photo.
     *
     * @param string $photo_filename              The filename of the photo.
     * @return string                             The pathname of the corresponding thumbnail.
     */
    function get_photo_thumbnail_path($photo_filename)
    {
        if (!empty($photo_filename) )
        {
            $root = get_root_path();
    
            $folder = "$root/data/thumbnails";

            return "$folder/$photo_filename";
        }
        return '';
    }


    /**
     * Create a thumbnail image for the given photo.
     *
     * @param string $photo_filename              The filename of the photo.
     * @param boolean $replace_if_exists          true if the thumbnail should be replaced if it already exists; false otherwise.
     */
    function create_photo_thumbnail($photo_filename, $replace_if_exists = false)
    {
        $root = get_root_path();

        $default_image_filename = get_photo_pathname();

        $thumbnail_pathname = get_photo_thumbnail_path($photo_filename);
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


    /**
     * Get the size of the given image.
     *
     * @param string $filename                    The filename of the specified file.
     * @return array                              The size of the specified file.
     */
    function get_image_size($filename)
    {
        $photo_size = array();

        if ($filename !== '')
        {
            $root = get_root_path();

            // Work out the size of the image
            if (file_exists($root.$filename) )
            {
                $photo_size = getimagesize($root.$filename);
            }
        }
        return $photo_size;
    }


    /**
     * Get the relative pathname of the image file which should be used given the specified photo filename.
     *
     * @param string $filename                    The filename of the photo.
     * @return string                             The relative pathname of the photo, or trans_flag.jpg if empty.
     */
    function get_photo_pathname($filename = '')
    {
        $pathname = '/images/trans_flag.jpg';

        if ($filename !== '')
        {
            $pathname = "/data/photos/$filename";
        }
        return $pathname;
    }


    /**
     * Get the relative pathname of the thumbnail image file which should be used given the specified photo filename.
     *
     * @param string $filename                    The filename of the photo.
     * @return string                             The relative pathname of the thumbnail, or trans_flag.jpg if empty.
     */
    function get_thumbnail_pathname($filename = '')
    {
        if (!empty($filename) )
        {
            return '/data/thumbnails/'.$filename;
        }
        return get_photo_pathname('');
    }



    /**
     * Get the relative pathname of the QR code image file which should be used given the specified UID.
     *
     * @param string $uid                         The UID of the report.
     * @return string                             The relative pathname of the QRCode image file.
     */
    function get_qrcode_pathname($uid)
    {
        if (!empty($uid) )
        {
            return "/data/qrcodes/$uid.png";
        }
        return '';
    }


    /**
     * Return the dates bounding the given year, month and day.
     *
     * @param int $year                           The year.
     * @param int $month                          The month.
     * @param int $day                            The day.
     * @return array                              An array containing the start and end dates bounding the given year, month and day, in ISO format.
     */
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


    /**
     * Return the dates bounding the date encoded in the given path (e.g. tdor.translivesmatter.info/reports/year/month/day/name_location_uid)
     *
     * @param string $path                        A URL encoding the specified date.
     * @return array                              An array containing the start and end dates bounding the given year, month and day, in ISO format.
     */
    function get_date_range_from_url($path)
    {
        $range = array();

        if (ENABLE_FRIENDLY_URLS)
        {
            $elements = explode('/', $path);                // Split path on slashes

            // e.g. tdor.translivesmatter.info/reports/year/month/day/name_location_uid
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


    /**
     * Get the friendly URL fo the given report and action.
     *
     * @param Report $report                      The report for which the URL should be returned.
     * @param Report $action                      The correponding action.
     * @return string                             The friendly URL.
     */
    function get_friendly_link($report, $action)
    {
        $date = new DateTime($report->date);

        $hyphen = '-';
        $underscore = '_';

        $place      = $report->has_location() ? $report->location.$hyphen.$report->country : $report->country;

        $main_field = strtolower(replace_accents($report->name.$underscore.$place) );

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


    /**
     * Get the raw URL fo the given report and action.
     *
     * @param Report $report                      The report for which the URL should be returned.
     * @param Report $action                      The correponding action.
     * @return string                             The raw URL.
     */
    function get_permalink($report, $action = 'show')
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


    /**
     * Return the UID from the given friendly URL. The URL is encoded at the end of the URL
     *
     * @param string $url                         The friendly URL
     * @return string                             The corresponding UID.
     */
    function get_uid_from_friendly_url($url)
    {
        $url   = ltrim($url, '/');                             // Trim leading slash(es)...
        $elements = explode('/', $url);                // Split path on slashes

        // e.g. tdor.transllivesmatter.info/reports/year/month/day/name
        $element_count = count($elements);

        if ( ($element_count >= 1) && ( ($elements[0] == 'reports') || ($elements[0] == 'posts') ) )
        {
            //$year       = $elements[1];
            //$month      = $elements[2];
            //$day        = $elements[3];

            //$name       = urldecode($elements[4]);

            //$query_pos = strpos($name, '?');

            //if ($query_pos > 0)
            //{
            //    // Strip off the queries
            //    $name = substr($name, 0, $query_pos);
            //}

            //$name_len   = strlen($name);

            $query_pos = strpos($url, '?');

            if ($query_pos > 0)
            {
                // Strip off the queries
                $url = substr($url, 0, $query_pos);
            }

            $name_len   = strlen($url);

            $uid_len = 8;
            $uid_delimiter_pos = $name_len - ($uid_len + 1);

            if ( ($name_len > $uid_len) && ( ($url[$uid_delimiter_pos] === '-') || ($url[$uid_delimiter_pos] === '_') ) )
            {
                $uid = substr($url, -$uid_len);

                // Validate
                if (is_valid_hex_string($uid) )
                {
                    return $uid;
                }
            }
        }
        return '';
    }


   /**
     * Return tweet text for the given report.
     *
     * @param Report $report                      The report
     * @return string                             The corresponding tweet text.
     */
    function get_tweet_text($report)
    {
        $newline    = "\n";

        $text       = $report->tweet;

        if (empty($text) )
        {
            $date       = get_display_date($report);
            $cause      = get_displayed_cause_of_death($report);
            $place      = $report->has_location() ? "$report->location ($report->country)" : $report->country;

            $text       = $report->name;

            $text      .= " $cause";
            $text      .= " in $place";
            $text      .= " on $date.";

            if (!empty($report->age) )
            {
                $text  .= $newline.$newline."They were $report->age.";
            }

            $text  .=  ' #SayTheirName #TransLivesMatter #TDoR';
        }
        return $text;
    }


   /**
     * Return summary text for the given report. This text is used on the slider, thumbnails view and slideshow.
     *
     * @param Report $report                      The report
     * @return array                              An array containing the corresponding summary text, with the following fields: 'title', 'desc', 'date' and 'location'.
     */
    function get_summary_text($report)
    {
        $date           = get_display_date($report);
        $place          = $report->has_location() ? "$report->location, $report->country" : $report->country;
        $desc           = $report->name;

        if ($report->age !== '')
        {
            $desc .= " was $report->age and";
        }

        $desc      .= ' '.get_displayed_cause_of_death($report);
        $desc      .= " in $place";

        $title      = "$report->name ($date)";

        return array('title' => $title,
                     'desc' => $desc,
                     'date' => $date,
                     'location' => $place);
    }


    /**
     * Return a random ("get be out of here") URL to be linked to from the content warning.
     *
     * @return string                             A random URL.
     */
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


    /**
     * Generate HTML for social media links.
     *
     * @param string $url                         The URL of the report.
     * @param string $text                        Optional text for the Twitter link.
     * @param string $qrcode_uid                  The UID of the report.
     * @return string                             The generated HTML.
     */
    function show_social_links($url, $text = '', $qrcode_uid = '')
    {
        $encoded_url = rawurlencode($url);

        if (empty($text) )
        {
            $text = $url;
        }

        echo '<div id="social_links">';
        echo   "<a href='https://www.facebook.com/sharer/sharer.php?u=$encoded_url' title='Share on Facebook' target='_blank' rel='noopener'><img src='/images/social/facebook.svg' /></a>";
        echo   "<a href='https://twitter.com/intent/tweet?text=$text' title='Tweet about this' target='_blank' rel='noopener'><img src='/images/social/twitter.svg' /></a>";

        if (!empty($qrcode_uid) )
        {
            $qrcode_url = "javascript:show_id('qrcode_$qrcode_uid');";

            echo '<a href="'.$qrcode_url.'"><img src="/images/scan_qrcode.png" /></a>';
        }

        echo '</div>';
    }


    /**
     * Generate HTML for social media links.
     *
     * @param Report $report                      The report.
     * @return string                             The generated HTML.
     */
    function show_social_links_for_report($report)
    {
        $url            = get_host().get_permalink($report);

        $summary_text   = get_summary_text($report);
        $newline        = "\n";

        $tweet          = !empty($report->tweet) ?  get_tweet_text($report) : $summary_text['desc'].' ('.$summary_text['date'].').';

        $tweet          = rawurlencode($tweet.$newline.$newline.$url);

        show_social_links($url, $tweet, $report->uid);
    }


    /**
     * Return the string 'report' or 'reports' based on the value of the $count.
     *
     * @param string $count                 The count.
     * @return string                       'report' or 'reports' as appropriate.
     */
    function get_report_count_caption($count)
    {
        if ($count == 1)
        {
            return 'report';
        }
        return 'reports';
    }


    /**
     * Get the HTML <option> code for the given id, name and selection.
     *
     * @param string $id                    The id of the option.
     * @param string $name                  The name of the option.
     * @param boolean $selected             true if selected; false otherwise.
     * @return string                       The HTML text of the <option> element.
     */
    function get_combobox_option_code($id, $name, $selected)
    {
        $selected_attr = '';

        if ($selected)
        {
            $selected_attr = ' selected="selected"';
        }

        return '<option value="'.$id.'"'.$selected_attr.'>'.$name.'</option>';
    }


    /**
     * Get the HTML code for a <select> element for the "view as" combobox.
     *
     * The options available include the given TDoR years and an option for a custom date range.
     *
     * @param string $selection             The selection.
     * @param string $attribs               Additional attributes (onchange etc.) to apply to the control.
     * @return string                       The HTML text of the <select> element.
     */
    function get_view_combobox_code($selection, $attribs = '')
    {
        $code ='<select id="view_as" name="View as" '.$attribs.'>';            // 'onchange="go();"'

        $code .= get_combobox_option_code('list',       'List',         ($selection === 'list')         ? true : false);
        $code .= get_combobox_option_code('thumbnails', 'Thumbnails',   ($selection === 'thumbnails')   ? true : false);
        $code .= get_combobox_option_code('map',        'Map',          ($selection === 'map')          ? true : false);
        $code .= get_combobox_option_code('details',    'Details',      ($selection === 'details')      ? true : false);

        $code .= '</select>';

        return $code;
    }




?>