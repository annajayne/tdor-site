<?php
    /**
     * Edit the current report.
     *
     */

    require_once('geocode.php');
    require_once('models/report_utils.php');
    require_once('models/report_events.php');


    /**
     * Determine if the given report has been changed as a result of editing.
     *
     * @param Report $report                The original report.
     * @param Report $updated_report        The edited report.
     * @return boolean                      true if changed; false otherwise.
     */
    function is_report_edited($report, $updated_report)
    {
        if ( ($updated_report->uid !== $report->uid) ||
             ($updated_report->draft !== $report->draft) ||
             ($updated_report->name !== $report->name) ||
             ($updated_report->age !== $report->age) ||
             ($updated_report->birthdate !== $report->birthdate) ||
             ($updated_report->photo_filename !== $report->photo_filename) ||
             ($updated_report->photo_source !== $report->photo_source) ||
             ($updated_report->date !== $report->date) ||
             ($updated_report->source_ref !== $report->source_ref) ||
             ($updated_report->location !== $report->location) ||
             ($updated_report->country !== $report->country) ||
             ($updated_report->latitude !== $report->latitude) ||
             ($updated_report->longitude !== $report->longitude) ||
             ($updated_report->category !== $report->category) ||
             ($updated_report->cause !== $report->cause) ||
             ($updated_report->description !== $report->description) ||
             ($updated_report->tweet !== $report->tweet) )
        {
            return true;
        }
        return false;
    }


    if (is_editor_user() )
    {
        $db                         = new db_credentials();
        $reports_table              = new Reports($db);

        $locations                  = $reports_table->get_locations();
        $countries                  = $reports_table->get_countries();
        $categories                 = $reports_table->get_categories();
        $causes                     = $reports_table->get_causes();

        if (isset($_POST['submit']) )
        {
            $updated_report = new Report;
            $updated_report->set_from_report($report);

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

                    $updated_report->uid    = $uid;
                }
            }
            $updated_report->name           = $_POST['name'];
            $updated_report->age            = $_POST['age'];
            $updated_report->birthdate      = $_POST['birthdate'];
            $updated_report->photo_source   = $_POST['photo_source'];
            $updated_report->date           = date_str_to_iso($_POST['date']);
            $updated_report->source_ref     = $_POST['source_ref'];
            $updated_report->location       = $_POST['location'];
            $updated_report->country        = $_POST['country'];
            $updated_report->country_code   = get_country_code($report->country);
            $updated_report->latitude       = $_POST['latitude'];
            $updated_report->longitude      = $_POST['longitude'];
            $updated_report->category       = strtolower($_POST['category']);
            $updated_report->cause          = strtolower($_POST['cause']);
            $updated_report->description    = $_POST['description'];
            $updated_report->tweet          = $_POST['tweet'];
            $updated_report->date_updated   = date("Y-m-d");
            $updated_report->permalink      = get_permalink($updated_report);

            if (empty($updated_report->category) )
            {
                // If the category is blank, update it (I don't think this can happen now, so this is contingency code)
                $updated_report->category = Report::get_category($updated_report);
            }

            // Generate/update QR code image file
            create_qrcode_for_report($report);

            if (isset($_FILES["photo"]) )
            {
                if (is_photo_upload_valid($_FILES["photo"]) )
                {
                    $extension              = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION) );

                    $data_dir               = "data";
                    $photos_dir             = "$data_dir/photos/";
                    $thumbnails_dir         = "$data_dir/thumbnails/";

                    $target_filename        = generate_photo_filename($report, $extension);
                    $target_pathname        = $photos_dir.$target_filename;

                    // If the target file exists, replace it
                    if (file_exists($target_pathname) )
                    {
                        unlink($target_pathname);
                    }

                    // If there was a photo previously, remove it and the corresponding thumbnail.
                    if (!empty($report->photo_filename) )
                    {
                        $existing_photo_pathname = $photos_dir.$report->photo_filename;

                        if (file_exists($existing_photo_pathname) )
                        {
                            unlink($existing_photo_pathname);
                        }

                        $existing_thumbnail_pathname = "$thumbnails_dir/$report->photo_filename";

                        if (file_exists($existing_thumbnail_pathname) )
                        {
                            unlink($existing_thumbnail_pathname);
                        }
                    }

                    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_pathname) )
                    {
                        // Photo uploaded - also generate thumbnail image
                        create_photo_thumbnail($target_filename, true);

                        $updated_report->photo_filename = $target_filename;
                    }
                }
            }

            if (is_report_edited($report, $updated_report) )
            {
                if ($reports_table->update($updated_report) )
                {
                    ReportEvents::report_updated($updated_report);

                    redirect_to($report->permalink);
                }
            }
        }

        echo '<h2>Edit Report</h2><br>';

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
        echo       '<div id="photo-placeholder">';

        if (!empty($report->photo_filename) )
        {
            // If the photo matches one we already have, show it
            echo     '<br><br><img src="'.get_photo_pathname($report->photo_filename).'" />';
        }

        echo       '</div>';
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

        // Source ref
        echo     '<div class="grid_6">';
        echo       '<label for="source_ref">Source Ref:<br></label>';
        echo       '<input type="text" name="source_ref" id="source_ref" value="'.$report->source_ref.'" onkeyup="javascript:set_text_colours()" style="width:100%;" />';
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

        // Description
        echo     '<div class="grid_12">';
        echo       '<label for="description">Description:<br></label>';
        echo       '<textarea name="description" id="description" style="width:100%; height:500px;" onkeyup="javascript:set_text_colours()">'.$report->description.'</textarea>';
        echo     '</div>';

        // Short Description (memorial cards etc.)
        echo     '<div class="grid_12">';
        echo       '<label for="short_desc">Memorial Card Short Description:<br></label>';
        echo       '<ul><i><p id="short_desc">'.get_short_description($report).'</p></i></ul>';
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


