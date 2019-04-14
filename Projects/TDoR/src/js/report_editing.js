    // Report add/edit support
    //



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

    });


    // Stackedit markdown editor
    $.getScript('/js/libs/stackedit.min.js', function()
    {
        // sThe script is now loaded and executed - dependent JS follows.


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
