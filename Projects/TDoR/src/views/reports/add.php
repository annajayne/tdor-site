<?php
    /**
     * Add a new report.
     * 
     */
?>

<!-- Script -->
<script>
    $(document).ready(function()
    {
        $.datepicker.setDefaults(
        {
            dateFormat: 'dd M yy'
        });

        $(function()
        {
            $("#datepicker").datepicker();
        });

        $('#cancel').click(function()
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

        $report                     = new Report();

        $datenow                    = new DateTime('now');

        $report->uid                = get_random_hex_string();
        $report->name               = 'Name Unknown';
        $report->age                = '';
        $report->photo_filename     = '';
        $report->photo_source       = '';
        $report->date               = date_str_to_display_date($datenow->format('d M Y') );
        $report->tgeu_ref           = '';
        $report->location           = '';
        $report->country            = '';
        $report->cause              = '';
        $report->description        = '';
        $report->permalink          = get_permalink($report);

        if (isset($_POST['submit']) )
        {
            $report->name           = $_POST['name'];
            $report->age            = $_POST['age'];
            $report->photo_source   = $_POST['photo_source'];
            $report->date           = date_str_to_iso($_POST['date']);
            $report->tgeu_ref       = $_POST['tgeu_ref'];
            $report->location       = $_POST['location'];
            $report->country        = $_POST['country'];
            $report->cause          = strtolower($_POST['cause']);
            $report->description    = $_POST['description'];
            $report->permalink      = get_permalink($report);
            $report->date_created   = date("Y-m-d");

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

            if (Reports::add($report) )
            {
                echo "<script>window.location.href='$report->permalink'</script>";
            }
        }

        echo '<h2>Add Report</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';


        // Name
        echo     '<div class="grid_9">';
        echo       '<label for="name">Name:<br></label>';
        echo       '<input type="text" name="name" id="name" value="'.$report->name.'" style="width:100%;" />';
        echo     '</div>';

        // Age
        echo     '<div class="grid_3">';
        echo       '<label for="age">Age:<br></label>';
        echo       '<input type="text" name="age" id="age" value="'.$report->age.'" style="width:100%;" />';
        echo     '</div>';

        // Photo
        echo     '<div class="grid_12"><label for="photo_filename">Photo:<br></label>';
        echo       '<input type="file" name="photo" id="photoUpload" accept="image/png, image/jpeg" />';
        echo       '<div id="photo-placeholder"></div>';
        echo     '</div>';

        // Photo source
        echo     '<div class="grid_12">';
        echo       '<label for="photo_source">Photo source:<br></label>';
        echo       '<input type="text" name="photo_source" id="photo_source" value="'.$report->photo_source.'" style="width:100%;" />';
        echo     '</div>';

        // Date
        echo     '<div class="grid_6">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.date_str_to_display_date($report->date).'" />';
        echo     '</div>';

        // TGEU ref
        echo     '<div class="grid_6">';
        echo       '<label for="tgeu_ref">TGEU Ref:<br></label>';
        echo       '<input type="text" name="tgeu_ref" id="tgeu_ref" value="'.$report->tgeu_ref.'" style="width:100%;" />';
        echo     '</div>';

        // Location
        echo     '<div class="grid_6">';
        echo       '<label for="location">Location:<br></label>';
        echo       '<input type="text" name="location" id="location" list="locations" required value="'.$report->location.'" style="width:100%;" />';
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
        echo       '<textarea name="description" style="width:100%; height:500px;">'.$report->description.'</textarea>';
        echo     '</div>';

        // OK/Cancel
        echo     '<br>';
        echo     '<div class="grid_12" align="right">';
        echo       '<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;';
        echo       '<input type="button" name="cancel" id="cancel" value="Cancel" class="btn btn-success" onclick="javascript:history.back()" />';
        echo     '</div>';

        echo   '</div>';
        echo '</form>';
    }
?>
