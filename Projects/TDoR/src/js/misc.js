    // Miscellaneous functions
    //

    function set_session_cookie(name, value)
    {
        $.cookie(name, value, { path: '/' } );
    }


    // Delete confirmation prompt
    //
    function confirm_delete(url)
    {
        var result = confirm("Delete this report?");

        if (result)
        {
            window.location.href = url;

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
