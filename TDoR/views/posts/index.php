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

        $code .= get_combobox_option_code('list', 'List', ($selection === 'list') ? true : false);
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
function go()
{
    var From = $('#From').val();
    var to = $('#to').val();

    var e = document.getElementById("view_as");
    var view_as = e.options[e.selectedIndex].value;

    if (From != '' && to != '')
    {
        var url = 'index.php?controller=posts&action=index&from=' + From + '&to=' + to + '&view=' + view_as;

        window.location.href = url;
    }
    else
    {
        alert("Please select both start and end dates");
    }
}


$(document).ready(function ()
{
    $.datepicker.setDefaults(
    {
	    dateFormat: 'dd M yy'
	});

	$(function()
	{
		$("#From").datepicker();
		$("#to").datepicker();
	});

	$('#range').click(function()
	{
	    go();
	});
});
</script> 

<?php
    $view_as     = 'list';

    if (isset($_GET['view']) )
    {
        $view_as     = $_GET['view'];
    }

    $post_count = count($posts);

    if ($post_count > 0)
    {
        $start_date     = get_display_date($posts[0]);
        $end_date       = get_display_date($posts[$post_count - 1]);

        echo '<div class="grid_12">View as:<br />'.get_view_combobox_code($view_as).'</div>';

        echo '<div class="grid_6">From Date:<br /><input type="text" name="From" id="From" class="form-control" placeholder="From Date" value="'.$start_date.'" /></div>';
        echo '<div class="grid_6">To Date:<br /><input type="text" name="to" id="to" class="form-control" placeholder="To Date" value="'.$end_date.'" /> <input type="button" name="range" id="range" value="Apply" class="btn btn-success" /></div>';

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