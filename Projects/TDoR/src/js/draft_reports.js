    // Javascript functions to support the "Draft Reports" page.
    //

 
    function get_view_as_selection()
    {
        var ctrl = document.getElementById("view_as");

        return ctrl.options[ctrl.selectedIndex].value;
    }


    function get_filter_text()
    {
        var ctrl = document.getElementById("filter");

        return ctrl.value;
    }


    function get_url(view_as, filter)
    {
        var url = '/reports?action=drafts';

        url += '&view=' + view_as;
        url += '&filter=' + filter;

        return url;
    }


    function go()
    {
        var view_as = get_view_as_selection();
        var filter = get_filter_text();

        set_session_cookie('reports_view_as', view_as);
        set_session_cookie('reports_filter', filter);

        var url = get_url(view_as, filter);

        window.location.href = url;
    }


    $(document).ready(function()
    {
        $('#apply_filter').click(function()
        {
            go();
        });

    });
