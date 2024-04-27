    // Javascript functions to support the "Recent Reports" page.
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


    function get_max_results_text()
    {
        var ctrl = document.getElementById("max_results");

        return ctrl.value;
    }


    function get_url(view_as, filter, max_results)
    {
        var url = '/reports?action=recent';

        url += '&view=' + view_as;
        url += '&filter=' + filter;
        url += '&max_results=' + max_results;

        return url;
    }


    function go()
    {
        var view_as = get_view_as_selection();
        var filter = get_filter_text();
        var max_results = get_max_results_text();

        set_session_cookie('reports_view_as', view_as);
        set_session_cookie('reports_filter', filter);
        set_session_cookie('reports_max_results', max_results);

        var url = get_url(view_as, filter, max_results);

        window.location.href = url;
    }


    $(document).ready(function()
    {
        $('#apply_filter').click(function ()
        {
            go();
        });

        $('#apply_max_results').click(function ()
        {
            go();
        });

    });
