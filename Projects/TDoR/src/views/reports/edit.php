<?php
    /**
     * Edit the current report.
     *
     */

    require_once('geocode.php');


    /**
     * Determine if the given report has been changed as a result of editing.
     *
     * @param Report $report                The original report.
     * @param Report $updated_report        The edited report.
     * @return boolean                      true if changed; false otherwise.
     */
    function is_report_edited($report, $updated_report)
    {
        if ( ($updated_report->name !== $report->name) ||
             ($updated_report->age !== $report->age) ||
             ($updated_report->photo_filename !== $report->photo_filename) ||
             ($updated_report->photo_source !== $report->photo_source) ||
             ($updated_report->date !== $report->date) ||
             ($updated_report->source_ref !== $report->source_ref) ||
             ($updated_report->location !== $report->location) ||
             ($updated_report->country !== $report->country) ||
             ($updated_report->latitude !== $report->latitude) ||
             ($updated_report->longitude !== $report->longitude) ||
             ($updated_report->cause !== $report->cause) ||
             ($updated_report->description !== $report->description) )
        {
            return true;
        }
        return false;
    }

?>

<!-- Script -->
<script>
    $(document).ready(function ()
    {
        $.datepicker.setDefaults(
        {
            dateFormat: 'dd M yy'
        });

        $(function ()
        {
            $("#datepicker").datepicker();
        });

        $('#cancel').click(function ()
        {
            go();
        });


    });
</script>

<script>
    $(document).ready(function ()
    {
        $("#photoUpload").on('change', function ()
        {
            // Get count of selected files
            var countFiles = $(this)[0].files.length;

            var imgPath = $(this)[0].value;
            var extn = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
            var image_holder = $("#photo-placeholder");
            image_holder.empty();

            if ( extn == "png" || extn == "jpg" || extn == "jpeg")
            {
                if (typeof (FileReader) != "undefined")
                {
                    // loop through each file selected for upload (in practice, there will be only one)
                    for (var i = 0; i < countFiles; i++)
                    {
                        var reader = new FileReader();
                        reader.onload = function (e)
                        {
                            $("<img />", {
                            "src": e.target.result,
                            "class": "thumb-image"
                            }).appendTo(image_holder);
                        }

                        image_holder.show();
                        reader.readAsDataURL($(this)[0].files[i]);
                    }
                }
                else
                {
                    alert("This browser does not support FileReader.");
                }
            }
            else
            {
                alert("Please select only images");
            }
        });
      });
</script>


<?php
    if (is_logged_in() )
    {
        $locations                  = Reports::get_locations();
        $countries                  = Reports::get_countries();
        $causes                     = Reports::get_causes();

        if (isset($_POST['submit']) )
        {
            $updated_report = new Report;
            $updated_report->set_from_report($report);

            $updated_report->name           = $_POST['name'];
            $updated_report->age            = $_POST['age'];
            $updated_report->photo_source   = $_POST['photo_source'];
            $updated_report->date           = date_str_to_iso($_POST['date']);
            $updated_report->source_ref     = $_POST['source_ref'];
            $updated_report->location       = $_POST['location'];
            $updated_report->country        = $_POST['country'];
            $updated_report->latitude       = $_POST['latitude'];
            $updated_report->longitude      = $_POST['longitude'];
            $updated_report->cause          = strtolower($_POST['cause']);
            $updated_report->description    = $_POST['description'];
            $updated_report->date_updated   = date("Y-m-d");

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
                Reports::update($updated_report);

                echo "<script>window.location.href='$report->permalink'</script>";
            }
        }

        echo '<h2>Edit Report</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';

        // Name
        echo     '<div class="grid_9">';
        echo       '<label for="name">Name:<br></label>';
        echo       '<input type="text" name="name" id="name" value="'.htmlspecialchars($report->name).'" style="width:100%;" />';
        echo     '</div>';

        // Age
        echo     '<div class="grid_3">';
        echo       '<label for="age">Age:<br></label>';
        echo       '<input type="text" name="age" id="age" value="'.$report->age.'" style="width:100%;" />';
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
        echo       '<input type="text" name="photo_source" id="photo_source" value="'.$report->photo_source.'"  style="width:100%;" />';
        echo     '</div>';

        // Date
        echo     '<div class="grid_6">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.date_str_to_display_date($report->date).'" />';
        echo     '</div>';

        // Source ref
        echo     '<div class="grid_6">';
        echo       '<label for="source_ref">Source Ref:<br></label>';
        echo       '<input type="text" name="source_ref" id="source_ref" value="'.$report->source_ref.'" style="width:100%;" />';
        echo     '</div>';

        // Location
        echo     '<div class="grid_6">';
        echo       '<label for="location">Location:<br></label>';
        echo       '<input type="text" name="location" id="location" list="locations" value="'.$report->location.'" style="width:100%;" />';
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
        echo       '<input type="text" name="country" id="country" list="countries" required value="'.$report->country.'" style="width:100%;" />';
        echo       '<datalist id="countries">';
        foreach ($countries as $country)
        {
            echo     '<option value="'.$country.'">';
        }
        echo       '</datalist>';
        echo      '</div>';

        // Latitude
        echo     '<div class="grid_6">';
        echo       '<label for="source_ref">Latitude:<br></label>';
        echo       '<input type="text" name="latitude" id="latitude" value="'.$report->latitude.'" style="width:100%;" />';
        echo      '</div>';

        // Longitude
        echo     '<div class="grid_6">';
        echo       '<label for="source_ref">Longitude:<br></label>';
        echo       '<input type="text" name="longitude" id="longitude" value="'.$report->longitude.'" style="width:100%;" />';
        echo      '</div>';

        // Cause
        echo     '<div class="grid_6">';
        echo       '<label for="cause">Cause of death:<br></label>';
        echo       '<input type="text" name="cause" id="cause" list="causes" required value="'.$report->cause.'" style="width:100%;" />';
        echo       '<datalist id="causes">';
        foreach ($causes as $cause)
        {
            echo     '<option value="'.$cause.'">';
        }
        echo       '</datalist>';
        echo      '</div>';

        // Description
        echo     '<div class="grid_12">';
        echo       '<label for="description">Description:<br></label>';
        echo       '<textarea name="description" style="width:100%; height:500px;">'.$report->description.'</textarea></div>';
        echo     '<br>';

        // OK/Cancel
        echo     '<div class="grid_12" align="right">';
        echo       '<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;';
        echo       '<input type="button" name="cancel" id="cancel" value="Cancel" class="btn btn-success" onclick="javascript:history.back()" />';
        echo     '</div>';

        echo   '</div>';
        echo '</form>';
    }
?>


