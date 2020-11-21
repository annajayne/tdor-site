    // Javascript functions to support the "Reports" page.
    //

 
    function get_tdor_year_selection()
    {
        var ctrl = document.getElementById("tdor_period");

        return ctrl.options[ctrl.selectedIndex].value;
    }


    function get_country_selection()
    {
        var ctrl = document.getElementById("country");

        return ctrl.options[ctrl.selectedIndex].value;
    }


    function get_category_selection()
    {
        var ctrl = document.getElementById("category");

        return ctrl.options[ctrl.selectedIndex].value;
    }


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


    function get_url(tdor_period, from_date, to_date, country, category, view_as, filter)
    {
        var url = '/reports';

        if (tdor_period > 0)
        {
            url += '/tdor' + tdor_period + '?';
        }

        else
        {
            url += '?from=' + from_date + '&to=' + to_date + '&';
        }

        url += 'country=' + country;
        url += '&category=' + category;
        url += '&view=' + view_as;
        url += '&filter=' + filter;

        return url;
    }


    function onselchange_tdor_year()
    {
        var year = get_tdor_year_selection();

        if ($.isNumeric(year) )
        {
            // NB no need to hide date pickers here as PHP deals with that for us once the page reloads.
            from_date   = (year - 1) + '-10-01';
            to_date     = year + '-09-30';

            if (year <= 1998)
            {
                from_date   = '1901-01-01';
                to_date     = '1998-09-30';
            }

            set_session_cookie('reports_date_from', from_date);
            set_session_cookie('reports_date_to', to_date);

            var url = get_url(year, from_date, to_date, get_country_selection(), get_category_selection(), get_view_as_selection(), get_filter_text());

            window.location.href = url;
        }
        else
        {
            // Show the date picker div
            var ctrl = document.getElementById("datepickers");

            ctrl.style = "display:inline;";
        }
    }



    function go()
    {
        var tdor_period = 0;

        var from_date   = $('#datepicker_from').val();
        var to_date     = $('#datepicker_to').val();

        var country     = get_country_selection();
        var category    = get_category_selection();
        var view_as     = get_view_as_selection();
        var filter      = get_filter_text();

        set_session_cookie('reports_country', country);
        set_session_cookie('reports_category', category);
        set_session_cookie('reports_view_as', view_as);
        set_session_cookie('reports_filter', filter);

        if (from_date != '' && to_date != '')
        {
            set_session_cookie('reports_date_from', from_date);
            set_session_cookie('reports_date_to', to_date);

            var url = get_url(tdor_period, date_to_iso(from_date), date_to_iso(to_date), country, category, view_as, filter);

            window.location.href = url;
        }
        else
        {
            alert("Please select both start and end dates");
        }
    }


    $(document).ready(function()
    {
        $.datepicker.setDefaults(
        {
            dateFormat: 'dd M yy'
        });

        $(function()
        {
            $("#datepicker_from").datepicker();
            $("#datepicker_to").datepicker();
        });

        $('#apply_range').click(function()
        {
            go();
        });

        $('#apply_filter').click(function()
        {
            go();
        });

    });
