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


    function go()
    {
        var from_date   = $('#datepicker_from').val();
        var to_date     = $('#datepicker_to').val();
    
        var e           = document.getElementById("view_as");
        var view_as     = e.options[e.selectedIndex].value;

        var filter_ctrl = document.getElementById("filter");
        var filter      = filter_ctrl.value;

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

    if ($post_count > 0)
    {
        $start_date     = get_display_date($posts[0]);
        $end_date       = get_display_date($posts[$post_count - 1]);

        echo '<div class="grid_6">From Date:<br /><input type="text" name="datepicker_from" id="datepicker_from" class="form-control" placeholder="From Date" value="'.$start_date.'" /></div>';
        echo '<div class="grid_6">To Date:<br /><input type="text" name="datepicker_to" id="datepicker_to" class="form-control" placeholder="To Date" value="'.$end_date.'" /> <input type="button" name="apply_range" id="apply_range" value="Apply" class="btn btn-success" /></div>';

        echo '<div class="grid_6">View as:<br />'.get_view_combobox_code($view_as).'</div>';
        echo '<div class="grid_6">Filter:<br /><input type="text" name="filter" id="filter" value="'.$filter.'" /> <input type="button" name="apply_filter" id="apply_filter" value="Apply" class="btn btn-success" /></div>';

        echo '<hr><br>';

        echo '<b>'.count($posts).' records found</b><br><br>';

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