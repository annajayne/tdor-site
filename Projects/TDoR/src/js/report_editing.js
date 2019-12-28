    // Report add/edit support
    //

    function set_orig_short_desc(short_desc)
    {
        window.orig_short_desc = decodeURI(short_desc);
    }


    function get_short_description(desc)
    {
        short_desc = desc;

        max_len = 180;

        pos = desc.indexOf("\n");
        if (pos > 0)
        {
            short_desc = desc.substring(0, pos);
        }

        if (short_desc.length > max_len)
        {
            temp = short_desc.substring(0, max_len);

            short_desc = temp.substring(0, temp.lastIndexOf(' ') ) + '...';
        }
        return short_desc;
    }


    function set_text_colour(id, changed_clr, unchanged_clr)
    {
        ctrl = document.getElementById(id);

        ctrl.style.color = (ctrl.value != ctrl.defaultValue) ? changed_clr : unchanged_clr;
    }


    function set_text_colours()
    {
        const unchanged_clr         = '#000000';
        const changed_clr           = '#0000FF';

        const textareaEl            = document.querySelector('textarea');
        desc                        = textareaEl.value;

        // Set the colour of the "Short Desc" element appropriately.
        // As this is plaintext rather than an input element, we can't use the set_text_colour() function below.
        short_desc_ctrl             = document.getElementById('short_desc');

        short_desc                  = get_short_description(desc);
        short_desc_ctrl.innerHTML   = short_desc;
        short_desc_ctrl.style.color = (short_desc != window.orig_short_desc) ? changed_clr : unchanged_clr;

        // Input elements
        const ids =
            [
                'name',
                'age',
                'photo_source', 
                'datepicker', 
                'source_ref', 
                'location', 
                'country', 
                'latitude',
                'longitude',
                'category',
                'cause',
                'description',
                'tweet'
            ];

        ids.forEach(function(id)
        {
            set_text_colour(id, changed_clr, unchanged_clr);
        });
    }


    function cause_changed()
    {
        const cause_ctrl = document.getElementById("cause");

        const cause = cause_ctrl.value;

        const url = "/api/report_category_service.php";

        var formData =
        {
            'cause': cause
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
                    if (response.category != "")
                    {
                        const category_ctrl = document.getElementById("category");

                        category_ctrl.value = response.category;

                        set_text_colours();
                    }
                }
                else
                {
                    alert('Unable to lookup report category');
                }
            },
            error: function (xhr, status, error)
            {
                alert('Error while looking up report category');
            }
        });
    }


    $(document).ready(function()
    {
        // Lookup approx Lat/Lon coordinates corresponding to the current location and country.
        function lookup_coords()
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


        function default_tweet_text()
        {
            var name        = document.getElementById("name").value;
            var age         = document.getElementById("age").value;
            var date        = document.getElementById("datepicker").value;
            var location    = document.getElementById("location").value;
            var country     = document.getElementById("country").value;
            var cause       = document.getElementById("cause").value;

            var url         = "/api/tweet_text_service.php";

            var formData =
            {
                'name': name,
                'age': age,
                'date': date,
                'location': location,
                'country': country,
                'cause': cause
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
                        tweet_ctrl         = document.getElementById("tweet");

                        tweet_ctrl.value   = response.tweet;

                        set_text_colours();
                    }
                    else
                    {
                        alert('Unable to lookup default tweet text');
                    }
                },
                error: function (xhr, status, error)
                {
                    alert('Error while looking up default tweet text');
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


        $('#default_tweet_text').click(function ()
        {
            default_tweet_text();
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
