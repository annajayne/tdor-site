    // Miscellaneous functions
    //

    function set_session_cookie(name, value)
    {
        $.cookie(name, value, { path: '/' } );
    }


    function date_to_iso(date_str)
    {
        var d = new Date(date_str);

        return d.getFullYear() + '-' +
                   ('0'+ (d.getMonth() + 1) ).slice(-2) + '-' +
                   ('0'+ d.getDate() ).slice(-2);
        return n;
    }


    // Delete confirmation prompt
    //
    function confirm_delete(prompt_text, redirect_to_url)
    {
        var result = confirm(prompt_text);

        if (result)
        {
            window.location.href = redirect_to_url;

            return true;
        }
        return false;
    }


    function show_id(id)
    {
        var el = $('#' + id);

        el.show();
    }


    function hide_id(id)
    {
        var el = $('#' + id);

        el.hide();
    }
