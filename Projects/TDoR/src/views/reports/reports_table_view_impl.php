<?php
     /**
     * Report view implementation.
     *
     */


    /**
     * Show an item in the table for the given report.
     *
     * @param Report $report                The report to display details for.
     */
    function show_summary_table_item($report)
    {
        $place              = $report->has_location() ? $report->location : '-';

        $link_url           = get_permalink($report);

        echo "<tr>";

        $name =$report->name;
        if ($report->deleted)
        {
            $name .= ' [Deleted]';
        }

        echo "<td style='white-space: nowrap;' sorttable_customkey='$report->date'>". get_display_date($report)."</td>";
        echo "<td><a href='".$link_url."'>".$name."</a></td>";
        echo "<td align='center'>". $report->age."</td>";

        echo "<td>". htmlspecialchars($place, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>". htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>". $report->category."</td>";
        echo "<td>". $report->cause."</td>";

        if (is_editor_user() )
        {
            $menuitems[] = array('href' => get_permalink($report, 'edit'),
                        'rel' => 'nofollow',
                        'text' => 'Edit');

            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).' ';
            }

            echo '<td align="center" class="nonprinting">'.$menu_html.'</td>';
        }

        echo "</tr>";
    }


    /**
     * Show the header row of the table for the given reports.
     *
     */
    function show_summary_table_header()
    {
        $columns = array('Date', 'Name', 'Age', 'Location', 'Country', 'Category', 'Cause');

        if (is_logged_in() )
        {
            $columns[] = 'Action';
        }

        $headings = '';

        foreach ($columns as $column)
        {
            $align = 'left';
            $class = '';

            switch ($column)
            {
                case 'Age':
                    $align='center';
                    break;
                    
                case 'Action':
                    $class='nonprinting';
                    break;
            }

            $headings .= "<th align='$align' class='$class'>$column</th>";
        }

        echo '<tr>'.$headings.'</tr>';
    }


    /**
     * Show a table of the given reports.
     *
     * @param array $reports                An array containing the given reports.
     *
     */
    function show_summary_table($reports)
    {
        echo '<div class="grid12"><div class="reports_table">';
        echo '<table class="sortable">';

        show_summary_table_header();

        foreach ($reports as $report)
        {
            show_summary_table_item($report);
        }

        echo '</table></div></div>';
    }


?>
