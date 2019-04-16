    // Report add/edit support
    //


    function set_text_colour(id)
    {
        ctrl = document.getElementById(id);

        var unchanged_clr = '#000000';
        var changed_clr = '#0000FF';

        ctrl.style.color = (ctrl.value != ctrl.defaultValue) ? changed_clr : unchanged_clr;
    }


    function set_text_colours()
    {
        set_text_colour('name');
        set_text_colour('age');
        set_text_colour('photo_source');
        set_text_colour('datepicker');
        set_text_colour('source_ref');
        set_text_colour('location');
        set_text_colour('country');
        set_text_colour('latitude');
        set_text_colour('longitude');
        set_text_colour('cause');
        set_text_colour('description');
    }


    $(document).ready(function()
    {



        function lookup_coords(location, country)
        {
            var location = document.getElementById("location").value;
            var country  = document.getElementById("country").value;

            var url      = "/api/geocode_service.php";

            var formData =
            {
                'location': location,
                'country': country
            };

            $.ajax(
            {
                type: 'POST',
                url: url,
                data: formData,
                dataType: 'JSON',
                encode: true,
                success: function (response, status, xhr)
                {
                    if (response.result)
                    {
                        latitude_ctrl = document.getElementById("latitude");
                        longitude_ctrl = document.getElementById("longitude");

                        latitude_ctrl.value  = response.latitude;
                        longitude_ctrl.value = response.longitude;

                        set_text_colours();
                    }
                    else
                    {
                        alert('Unable to lookup co-ordinates');
                    }
                },
                error: function (xhr, status, error)
                {
                    alert('Error while looking up co-ordinates');
                }
            });

        }


        $.datepicker.setDefaults(
        {
            dateFormat: 'dd M yy'
        });

        $(function()
        {
            $("#datepicker").datepicker(
            {
                onSelect: function(value, date)
                {
                    set_text_colours();
                }
            })
        });

        $('#lookup_coords').click(function()
        {
            lookup_coords();
        });

    });


    // Stackedit markdown editor
    $.getScript('/js/libs/stackedit.min.js', function()
    {
        // The script is now loaded and executed - dependent JS follows.


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

                set_text_colours();
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
    $(document).ready(function()
    {
        $("#photoUpload").on('change', function()
        {
            // Get the number of selected files
            var countFiles   = $(this)[0].files.length;

            var imgPath      = $(this)[0].value;
            var extn         = imgPath.substring(imgPath.lastIndexOf('.') + 1).toLowerCase();
            var image_holder = $("#photo-placeholder");

            image_holder.empty();

            if (extn == "png" || extn == "jpg" || extn == "jpeg")
            {
                if (typeof(FileReader) != "undefined")
                {
                    // Loop through each file selected for upload (in practice, there will be only one)
                    for (var i = 0; i < countFiles; i++)
                    {
                        var reader = new FileReader();
                        reader.onload = function(e)
                        {
                            $("<img />",
                            {
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
