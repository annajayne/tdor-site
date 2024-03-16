<?php
    /**
     * Add a new report.
     *
     */
    require_once('util/string_utils.php');                      // For get_random_hex_string()
    require_once('util/datetime_utils.php');                    // For date_str_to_iso() and date_str_to_display_date()
    require_once('util/geocode.php');
    require_once('models/report_utils.php');
    require_once('models/report_events.php');
    require_once('models/report_utils.php');
    require_once('models/report_events.php');


    if (is_editor_user() )
    {
        $db                         = new db_credentials();
        $reports_table              = new Reports($db);

        $locations                  = $reports_table->get_locations();
        $countries                  = $reports_table->get_countries();
        $categories                 = $reports_table->get_categories();
        $causes                     = $reports_table->get_causes();

        $report                     = new Report();

        $datenow                    = new DateTime('now');

        do
        {
            // Generate a new uid and check for clashes with existing entries
            $uid                    = get_random_hex_string();
            $id                     = $reports_table->find_id_from_uid($uid);                     // Check the existing table

            if ($id == 0)
            {
                $report->uid        = $uid;
            }
        } while (empty($report->uid) );

        $report->draft              = true;
        $report->name               = 'Name Unknown';
        $report->age                = '';
        $report->birthdate          = '';
        $report->photo_filename     = '';
        $report->photo_source       = '';
        $report->date               = date_str_to_display_date($datenow->format('d M Y') );
        $report->tdor_list_ref      = '';
        $report->location           = '';
        $report->country            = '';
        $report->category           = '';
        $report->cause              = '';
        $report->description        = '';
        $report->tweet              = '';
        $report->permalink          = get_permalink($report);

        if (isset($_POST['submit']) )
        {
            if (is_admin_user() )
            {
                $uid = $_POST['uid'];
                if (!empty($uid) )
                {
                    $uid_len = 8;
                    if (strlen($uid) > $uid_len)
                    {
                       $uid = substr($uid, -$uid_len);
                    }

                    $report->uid    = $uid;
                }
            }
            $report->name           = trim($_POST['name']);
            $report->age            = trim($_POST['age']);
            $report->birthdate      = trim($_POST['birthdate']);
            $report->photo_source   = trim($_POST['photo_source']);
            $report->date           = date_str_to_iso($_POST['date']);
            $report->tdor_list_ref  = trim($_POST['tdor_list_ref']);
            $report->location       = trim($_POST['location']);
            $report->country        = trim($_POST['country']);
            $report->country_code   = get_country_code($report->country);

            if (isset($_POST['latitude'] ) )
            {
                $report->latitude   = $_POST['latitude'];
            }
            if (isset($_POST['longitude'] ) )
            {
                $report->longitude  = $_POST['longitude'];
            }

            $report->category       = trim(strtolower($_POST['category']));
            $report->cause          = trim(strtolower($_POST['cause']));
            $report->description    = trim($_POST['description']);
            $report->tweet          = trim($_POST['tweet']);
            $report->permalink      = get_permalink($report);
            $report->date_created   = gmdate("Y-m-d H:i:s");

            if (empty($report->category) )
            {
                $report->category   = Report::get_category($report);
            }

            // Generate/update QR code image file
            create_qrcode_for_report($report);

            if (isset($_FILES["photo"]) )
            {
                if (is_photo_upload_valid($_FILES["photo"]) )
                {
                    $extension              = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION) );

                    $target_dir             = "data/photos/";
                    $target_filename        = generate_photo_filename($report, $extension);
                    $target_pathname        = $target_dir.$target_filename;

                    // If the target file exists, replace it
                    if (file_exists($target_pathname) )
                    {
                        unlink($target_pathname);
                    }

                    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_pathname) )
                    {
                        // Photo uploaded - also generate thumbnail image
                        create_photo_thumbnail($target_filename, true);

                        $report->photo_filename = $target_filename;
                    }
                }
            }

            // If a latitude and longitude weren't entered, determine them by geocoding.
            if (empty($report->longitude) || empty($report->longitude) )
            {
                $place = array();

                $place['location']  = $report->location;
                $place['country']   = $report->country;

                $places = array();
                $places[] = $place;

                $geocoded_places    = geocode(array($place) );

                if (!empty($geocoded_places) )
                {
                    $geocoded = $geocoded_places[0];

                    $report->latitude   = $geocoded['lat'];
                    $report->longitude  = $geocoded['lon'];
                }
                else
                {
                    $permalink  = get_permalink($report);
                    $date       = date_str_to_display_date($report->date);
                    $place      = !empty($report->location) ? "$report->location, $report->country" : $report->country;

                    echo "WARNING: Unable to geocode <a href='$report->permalink'><b>$report->name</b></a> ($report->date / $report->location ($report->country) )<br>";
                }
            }

            if ($reports_table->add($report) )
            {
                ReportEvents::report_added($report);

                redirect_to($report->permalink);
            }
        }

        echo '<h2>Add Report</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';


        // Name
        echo     '<div class="grid_6">';
        echo       '<label for="name">Name:<br></label>';
        echo       '<input type="text" name="name" id="name" value="'.htmlspecialchars($report->name).'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo     '</div>';

        // Age
        echo     '<div class="grid_3">';
        echo       '<label for="age">Age:<br></label>';
        echo       '<input type="text" name="age" id="age" value="'.$report->age.'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo     '</div>';

        // Birthdate
        echo     '<div class="grid_3">';
        echo       '<label for="birthdate">Birthdate:<br></label>';
        echo       '<input type="text" name="birthdate" id="birthdate" class="form-control" placeholder="Date" value="'.date_str_to_display_date($report->birthdate).'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo     '</div>';

        // Photo
        echo     '<div class="grid_12">';
        echo       '<label for="photo_filename">Photo:<br></label>';
        echo       '<input type="file" name="photo" id="photoUpload" accept="image/png, image/jpeg" />';
        echo       '<div id="photo-placeholder"></div>';
        echo     '</div>';

        // Photo source
        echo     '<div class="grid_12">';
        echo       '<label for="photo_source">Photo source:<br></label>';
        echo       '<input type="text" name="photo_source" id="photo_source" value="'.$report->photo_source.'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo     '</div>';

        // Date
        echo     '<div class="grid_6">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.date_str_to_display_date($report->date).'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';

        // TDoR list ref
        echo     '<div class="grid_6">';
        echo       '<label for="tdor_list_ref">TDoR list ref (if any):<br></label>';
        echo       '<input type="text" name="tdor_list_ref" id="tdor_list_ref" value="'.$report->tdor_list_ref.'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo     '</div>';

        // Location
        echo     '<div class="grid_6">';
        echo       '<label for="location">Location:<br></label>';
        echo       '<input type="text" name="location" id="location" list="locations" value="'.$report->location.'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo       '<datalist id="locations">';
        foreach ($locations as $location)
        {
            echo     '<option value="'.$location.'">';
        }
        echo       '</datalist>';
        echo     '</div>';

        // Country
        echo     '<div class="grid_6">';
        echo       '<label for="country">Country:<br></label>';
        echo       '<input type="text" name="country" id="country" list="countries" required value="'.$report->country.'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo       '<datalist id="countries">';
        foreach ($countries as $country)
        {
            echo     '<option value="'.$country.'">';
        }
        echo       '</datalist>';
        echo      '</div>';

        // Latitude
        echo     '<div class="grid_6">';
        echo       '<label for="latitude">Latitude:<br></label>';
        echo       '<input type="text" name="latitude" id="latitude" value="'.$report->latitude.'" onkeyup="javascript:set_text_colours()" style="width:80%;" />';
        echo      '</div>';

        // Longitude
        echo     '<div class="grid_6">';
        echo       '<label for="longitude">Longitude:<br></label>';
        echo       '<input type="text" name="longitude" id="longitude" value="'.$report->longitude.'" onkeyup="javascript:set_text_colours()" style="width:80%;" />';
        echo       '<input type="button" name="lookup_coords" id="lookup_coords" value="Lookup" class="btn btn-success" style="width:20%;" />';
        echo      '</div>';

        // Category
        echo     '<div class="grid_6">';
        echo       '<label for="category">Category:<br></label>';
        echo       '<input type="text" name="category" id="category" list="categories" required value="'.$report->category.'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
        echo       '<datalist id="categories">';
        foreach ($categories as $category)
        {
            echo     '<option value="'.$category.'">';
        }
        echo       '</datalist>';
        echo      '</div>';

        // Cause
        echo     '<div class="grid_6">';
        echo       '<label for="cause">Cause of death:<br></label>';
        echo       '<input type="text" name="cause" id="cause" list="causes" required value="'.$report->cause.'" onkeyup="javascript:set_text_colours()" onchange="javascript:cause_changed()" style="width:100%;" />';
        echo       '<datalist id="causes">';

        foreach ($causes as $cause)
        {
            echo     '<option value="'.$cause.'">';
        }
        echo       '</datalist>';
        echo      '</div>';

        if (is_admin_user() )
        {
            // UID
            echo     '<div class="grid_6">';
            echo       '<label for="uid">UID:<br></label>';
            echo       '<input type="text" name="uid" id="uid" value="'.$report->uid.'" onkeyup="javascript:uid_changed()" style="width:80%;" />';
            echo      '</div>';
        }

        // Short Description (memorial cards etc.)
        echo     '<div class="grid_12">';
        echo       '<label for="description">Memorial Card Short Description:<br></label>';
        echo       '<ul><i><p id="short_desc">'.get_short_description($report).'</p></i></ul>';
        echo     '</div>';

        // Description
        echo     '<div class="grid_12">';
        echo       '<label for="description">Description:<br></label>';
        echo       '<textarea name="description" id="description" style="width:100%; height:500px;" onkeyup="javascript:set_text_colours()">'.$report->description.'</textarea>';
        echo     '</div>';

        // Tweet text (optional)
        echo     '<div class="grid_12">';
        echo       '<label for="tweet">Tweet text (optional):</label><br>';
        echo       '<textarea name="tweet" id="tweet" maxlength="260" style="width:100%; height:100px;" onkeyup="javascript:set_text_colours()">'.$report->tweet.'</textarea>';
        echo       '<p>';
        echo         '<input type="button" name="default_tweet_text" id="default_tweet_text" value="Default" class="btn btn-success" style="width:10%;" />&nbsp;';
        echo         'Leave field blank for default text. Do not include report link as it will be added automatically.<br>';
        echo       '</p>';
        echo     '</div>';


        // OK/Cancel
        echo     '<br>';
        echo     '<div class="grid_12" align="right">';
        echo       '<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;';
        echo       '<input type="button" name="cancel" id="cancel" value="Cancel" class="btn btn-success" onclick="javascript:history.back()" />';
        echo     '</div>';

        echo   '</div>';
        echo '</form>';

        echo '<script src="/js/report_editing.js"></script>';
        echo "<script>set_orig_short_desc('".rawurlencode(get_short_description($report) )."');</script>";
    }
    else
    {
        redirect_to('/account/login');
    }
?>
