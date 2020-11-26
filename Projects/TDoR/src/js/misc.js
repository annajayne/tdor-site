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


    // Publish confirmation prompt
    //
    function confirm_publish(url)
    {
        var result = confirm("Publish this report?");

        if (result)
        {
            window.location.href = url;

            return true;
        }
        return false;
    }


    // Unpublish confirmation prompt
    //
    function confirm_unpublish(url)
    {
        var result = confirm("Unpublish this report?");

        if (result)
        {
            window.location.href = url;

            return true;
        }
        return false;
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
