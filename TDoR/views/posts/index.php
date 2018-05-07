<?php

    function get_combobox_option_code($id, $name, $selected)
    {
        $selected_attr = '';

        if ($selected)
        {
            $selected_attr = ' selected="selected"';
        }

        return '<option value="'.$id.'"'.$selected_attr.'>'.$name.'</option>';
    }


    function get_year_combobox_code($first_year, $last_year, $selection)
    {
        $code ='<select id="tdor_period" name="TDoR Period" onchange="onselchange_tdor_year();" >';

        for ($year = $first_year; $year <= $last_year; ++$year)
        {
            $label = 'TDoR '.$year.' (1 Oct '.($year - 1).' - 30 Sep '.$year. ')';

            $code .= get_combobox_option_code($year, $label, ($selection === $year) ? true : false);
        }

        $custom = 'custom';

        $code .= get_combobox_option_code($custom, 'Custom dates', ($selection === $custom) ? true : false);

        $code .= '</select>';

        return $code;
    }


    function get_view_combobox_code($selection)
    {
        $code ='<select id="view_as" name="View as" onchange="go();" >';

        $code .= get_combobox_option_code('list',    'List',    ($selection === 'list')    ? true : false);
        $code .= get_combobox_option_code('details', 'Details', ($selection === 'details') ? true : false);

        $code .= '</select>';

        return $code;
    }


    function get_item_url($item)
    {
        return '?controller=posts&action=show&id='.$item->id;
    }
?>


<!-- Script -->
<script>
    function get_tdor_year_selection()
    {
        var ctrl = document.getElementById("tdor_period");

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


    function get_url(from_date, to_date, view_as, filter)
    {
        var url = 'index.php?controller=posts&action=index';

        url += '&from=' + from_date + '&to=' + to_date;
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
            from_date   = '1 Oct ' + (year - 1);
            to_date     = '30 Sep ' + year;

            var url     = get_url(from_date, to_date, get_view_as_selection(), get_filter_text());

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
        var from_date   = $('#datepicker_from').val();
        var to_date     = $('#datepicker_to').val();

        if (from_date != '' && to_date != '')
        {
            var view_as = get_view_as_selection();
            var filter  = get_filter_text();

            var url     = get_url(from_date, to_date, view_as, filter);

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
</script>


<?php
    $view_as    = 'list';
    $filter     = '';

    if (isset($_GET['view']) )
    {
        $view_as = $_GET['view'];
    }

    if (isset($_GET["filter"]) )
    {
        $filter = $_GET["filter"];
    }

    $post_count = count($posts);

    if ($posts_available)
    {
        $tdor_first_year                = get_tdor_year(new DateTime($post_date_range[0]) );
        $tdor_last_year                 = get_tdor_year(new DateTime($post_date_range[1]) );

        $selected_year                  = $tdor_last_year;
        $display_date_pickers           = '';

        if (!empty($date_from) && !empty($date_to) )
        {
            $start_date                 = $date_from;
            $end_date                   = $date_to;

            if (str_begins_with($start_date, '1 Oct') && str_begins_with($end_date, '30 Sep') )
            {
                $selected_year          = get_tdor_year(new DateTime($start_date) );
                $display_date_pickers   = 'none';
            }
            else
            {
                $selected_year          = 'custom';
                $display_date_pickers   = 'inline';
            }
        }

        echo '<div class="grid_12">TDoR period:<br />'.get_year_combobox_code($tdor_first_year, $tdor_last_year, $selected_year).'</div>';

        echo '<div id="datepickers" style="display:'.$display_date_pickers.';">';
        echo '  <div class="grid_6">From Date:<br /><input type="text" name="datepicker_from" id="datepicker_from" class="form-control" placeholder="From Date" value="'.$start_date.'" /></div>';
        echo '  <div class="grid_6">To Date:<br /><input type="text" name="datepicker_to" id="datepicker_to" class="form-control" placeholder="To Date" value="'.$end_date.'" /> <input type="button" name="apply_range" id="apply_range" value="Apply" class="btn btn-success" /></div>';
        echo '</div>';

        echo '<div class="grid_6">View as:<br />'.get_view_combobox_code($view_as).'</div>';
        echo '<div class="grid_6">Filter:<br /><input type="text" name="filter" id="filter" value="'.$filter.'" /> <input type="button" name="apply_filter" id="apply_filter" value="Apply" class="btn btn-success" /></div>';

        echo '<hr><br>';

        echo '<b>'.$post_count.' records found</b><br><br>';
    }

    if ($post_count > 0)
    {
        switch ($view_as)
        {
            case 'list':
                show_summary_table($posts);
                break;

            case 'details':
                show_details($posts);
                break;
        }
    }
    else
    {
        echo '<br>No entries';
    }

?>