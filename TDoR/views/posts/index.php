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


    function print_summary_table_header()
    {
        $columns = array('Date', 'Name', 'Age', 'Photo', 'Location', 'Country', 'Cause');

        $headings = '';

        foreach ($columns as $column)
        {
            $align='left';

            switch ($column)
            {
                case 'Age':
                case 'Photo':
                    $align='center';
                    break;
            }

            $headings .= '<th align="'.$align.'">'.$column.'</th>';
        }

        print '<tr>'.$headings.'</tr>';
    }

    function get_item_url($item)
    {
        return '?controller=posts&action=show&id='.$item->id;
    }


    function print_summary_table_item($item, $photo_thumbnail, $width, $height)
    {
        $truncate_desc_to = 100;

        $truncated_desc = (strlen($item->description) > $truncate_desc_to) ? substr($item->description, 0, $truncate_desc_to).'...' : $item->description;

        $link_url = get_item_url($item);

        $img_tag = '';

        if ( ($width > 0) &&  ($height > 0) )
        {
            $img_tag = "<a href='".$link_url."'><img src='".$photo_thumbnail."' alt='".$item->name."' width='".$width."' height='".$height."' /></a>";
        }

        print "<tr>";

        print "<td style='white-space: nowrap;' sorttable_customkey='$item->date'>". get_display_date($item)."</td>";
        print "<td><a href='".$link_url."'>".$item->name."</a></td>";
        print "<td align='center'>". $item->age."</td>";
        print "<td>". $img_tag."</td>";
        //print "<td>". $item->tgeu_ref."</td>";
        print "<td>". $item->location."</td>";
        print "<td>". $item->country."</td>";
        print "<td>". $item->cause."</td>";
       // print "<td>". $truncated_desc."</td>";

        print "</tr>";
    }


    function print_summary_table($posts)
    {
        print '<div class="grid12"><div style="overflow-x:auto;">';
        print '<table class="sortable">';
        print_summary_table_header();

        $thumbnail_width_pixels = 150;

        foreach ($posts as $post)
        {
            $photo_pathname = '';
            $width = 0;
            $height = 0;

            if ($post->photo_filename !== '')
            {
                // Work out the size of the image
                $photo_pathname = "data/photos/".$post->photo_filename;
                if (file_exists($photo_pathname) )
                {
                    $photo_size = getimagesize($photo_pathname);

                    $width      = $photo_size[0];
                    $height     = $photo_size[1];

                    if ( ($width > 0) &&  ($height > 0) && ($width > $thumbnail_width_pixels) )
                    {
                        $height = $height / ($width / $thumbnail_width_pixels);
                        $width = $thumbnail_width_pixels;
                    }
                }
            }

            print_summary_table_item($post, $photo_pathname, $width, $height);
        }
        print '</table></div></div>';

    }


    function print_details($items)
    {
        foreach ($items as $item)
        {
            show_post($item, get_item_url($item) );
            echo '<hr>';
        }
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
		var From = $('#From').val();
		var to = $('#to').val();

		var e = document.getElementById("view_as");
		var view_as = e.options[e.selectedIndex].value;

		if (From != '' && to != '')
		{
		    var url = 'index.php?controller=posts&action=index&from=' + From + '&to=' + to + '&views=' + view_as;

		    window.location.href = url;
		}
		else
		{
			alert("Please select both start and end dates");
		}
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

        print '<div class="grid_12">View as:<br />'.get_view_combobox_code($view_as).'</div>';

        print '<div class="grid_6">From Date:<br /><input type="text" name="From" id="From" class="form-control" placeholder="From Date" value="'.$start_date.'" /></div>';
        print '<div class="grid_6">To Date:<br /><input type="text" name="to" id="to" class="form-control" placeholder="To Date" value="'.$end_date.'" /> <input type="button" name="range" id="range" value="Apply" class="btn btn-success" /></div>';

        print '<hr><br>';

        echo '<b>'.count($posts).' records found</b><br><br>';

        switch ($view_as)
        {
            case 'list':
                print_summary_table($posts);
                break;

            case 'details':
                print_details($posts);
                break;
        }
    }
    else
    {
        print '<br>No entries';
    }
?>