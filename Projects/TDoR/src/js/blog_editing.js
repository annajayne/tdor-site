    // Bogpost add/edit support
    //

    // Delete confirmation prompt
    //
    function confirm_delete_post(redirect_to_url)
    {
        return confirm_delete("Delete this blogpost?", redirect_to_url);
    }


    function set_text_colour(id, changed_clr, unchanged_clr)
    {
        ctrl = document.getElementById(id);

        if (ctrl != null)
        {
            ctrl.style.color = (ctrl.value != ctrl.defaultValue) ? changed_clr : unchanged_clr;
        }
    }


    function set_text_colours()
    {
        const unchanged_clr         = '#000000';
        const changed_clr           = '#0000FF';

        // Input elements
        const ids =
            [
                'title',
                'subtitle',
                'datepicker',
                'timepicker',
                'text'
            ];

        ids.forEach(function(id)
        {
            set_text_colour(id, changed_clr, unchanged_clr);
        });
    }


    $(document).ready(function ()
    {
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


        $(function()
        {
            $('#timepicker').timepicker();
            $('#timepicker').on('changeTime', function()
            {
                set_text_colours();
            });
        });

    });


    // Stackedit markdown editor
    $.getScript('/js/libs/stackedit.min.js', function()
    {   // The script is now loaded and executed - dependent JS follows.


        // Function to create the "Edit with StackEdit" link
        function makeEditButton(el)
        {
            const div = document.createElement('div');

            div.className = 'stackedit-button-wrapper';
            div.innerHTML = '<a href="javascript:void(0)"><img src="/images/stackedit.svg" width="24" height="24" style="margin-top:10px; margin-right:10px;">Edit/Preview with StackEdit</a>';

            el.parentNode.insertBefore(div, el.nextSibling);

            return div.getElementsByTagName('a')[0];
        }


        // Get a reference to the "content" textarea field
        const textareaEl = document.querySelector('textarea');
        
        if (textareaEl != null)
        {
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
        }
    });
