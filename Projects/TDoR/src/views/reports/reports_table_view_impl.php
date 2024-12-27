<?php
     /**
     * Report view implementation.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_display_date()


    /**
     * Show an item in the table for the given report.
     *
     * @param Report $report                The report to display details for.
     */
    function show_summary_table_item($report)
    {
        $place              = get_displayed_location($report);
        $link_url           = get_permalink($report);

        echo "<tr>";

        $qualifiers = '';

        if ($report->draft)
        {
            $qualifiers .= ' [Draft]';
        }

        if ($report->deleted)
        {
            $qualifiers .= ' [Deleted]';
        }

        echo "<td style='white-space: nowrap;' sorttable_customkey='$report->date'>". date_str_to_display_date($report->date)."</td>";
        echo "<td><a href='".$link_url."'>".$name = $report->name."</a>$qualifiers</td>";
        echo "<td align='center'>". $report->age."</td>";

        echo "<td>". htmlspecialchars($place, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>". htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>". $report->category."</td>";
        echo "<td>". $report->cause."</td>";

        if (is_admin_user() || is_editor_user())
        {
            $updated = $report->date_updated;

            echo '<td class="nonprinting">'.$report->date_updated.'</td>';
        }

        if (is_editor_user() )
        {
            $is_admin           = is_admin_user();

            $site_config        = get_config();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];

            $menuitems          = [];

            if (!$edits_disabled || $is_admin)
            {
                $menuitems[]        = array('href' => get_permalink($report, 'edit'),
                                            'rel' => 'nofollow',
                                            'text' => 'Edit');

                if ($report->draft)
                {
                    $menuitems[]    = array('href' => 'javascript:void(0);',
                                            'onclick' => 'confirm_publish(\''.get_permalink($report, 'publish').'\');',
                                            'rel' => 'nofollow',
                                            'text' => 'Publish');
                }
                else if ($is_admin)
                {
                    $menuitems[]    = array('href' => 'javascript:void(0);',
                                            'onclick' => 'confirm_unpublish(\''.get_permalink($report, 'unpublish').'\');',
                                            'rel' => 'nofollow',
                                            'text' => 'Unpublish');
                }
            }

            $menuitems[]     = array('href' => get_permalink($report, 'export'),
                            'rel' => 'nofollow',
                            'text' => 'Download');

            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).'<br>';
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

        if (is_admin_user() || is_editor_user())
        {
            $columns[] = 'Updated';
        }
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

                case 'Updated':
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
        echo '<div class="reports_table">';
        echo   '<table class="sortable">';

        show_summary_table_header();

        foreach ($reports as $report)
        {
            show_summary_table_item($report);
        }

        echo   '</table>';
        echo '</div>';
    }

?>
