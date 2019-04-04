<?php
    /**
     * Add a new report.
     * 
     */

    require_once('geocode.php');
?>

<!-- Scripts -->
<script src="/js/libs/stackedit.min.js"></script>
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


    // Stackedit markdown editor
    $(document).ready(function()
    {
        // Function to create the "Edit with StackEdit" link
        function makeEditButton(el)
        {
            const div = document.createElement('div');

            div.className = 'stackedit-button-wrapper';
            div.innerHTML = '<a href="javascript:void(0)"><img src="/images/stackedit.svg" width="24" height="24" style="margin-top:10px; margin-right:10px;">Edit/Preview with StackEdit</a>';

            el.parentNode.insertBefore(div, el.nextSibling);

            return div.getElementsByTagName('a')[0];
        }

        // Get a reference to the "Description" textarea field
        const textareaEl = document.querySelector('textarea');

        // Handler for the "Edit with StackEdit" link
        makeEditButton(textareaEl).addEventListener('click', function onClick()
        {
            const stackedit = new Stackedit();

            stackedit.on('fileChange', function onFileChange(file)
            {
                textareaEl.value = file.content.text;
            });

            stackedit.openFile(
            {
                name: '',
                content:
                {
                    text: textareaEl.value
                }
            });
        });

    });


    // Photo upload script
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

        do
        {
            // Generate a new uid and check for clashes with existing entries
            $uid                    = get_random_hex_string();
            $id                     = Reports::find_id_from_uid($uid);                     // Check the existing table

            if ($id == 0)
            {
                $report->uid        = $uid;
            }
        } while (empty($report->uid) );

        $report->name               = 'Name Unknown';
        $report->age                = '';
        $report->photo_filename     = '';
        $report->photo_source       = '';
        $report->date               = date_str_to_display_date($datenow->format('d M Y') );
        $report->source_ref           = '';
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
            $report->source_ref     = $_POST['source_ref'];
            $report->location       = $_POST['location'];
            $report->country        = $_POST['country'];

            if (isset($_POST['latitude'] ) )
            {
                $report->latitude   = $_POST['latitude'];
            }
            if (isset($_POST['longitude'] ) )
            {
                $report->longitude  = $_POST['longitude'];
            }

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
                    echo "WARNING: Unable to geocode <a href='$report->permalink'><b>$report->name</b></a> ($report->date / $report->location ($report->country) )<br>";
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
