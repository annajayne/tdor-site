<?php
     /**
     * Report view implementation.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_display_date()
    require_once('util/report_utils.php');                      // For is_contributed_report()


    /**
     * Show an item in the table for the given report.
     *
     * @param Report $report                The report to display details for.
     * @param boolean $contributed          Whether this user contributed this report
     */
    function show_summary_table_item($report, $contributed)
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

        $is_contributor = is_contributor_user();
        $is_editor = is_editor_user();
        $is_admin = is_admin_user();

        if ($is_admin || $is_editor)
        {
            $updated = $report->date_updated;

            echo '<td class="nonprinting">'.$report->date_updated.'</td>';
        }

        if ($is_contributor || $is_editor)
        {
            $site_config        = get_config();

            $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];

            $menuitems          = [];

            if (!$edits_disabled || $is_admin)
            {
                // Enable the "Edit Report" button for Editors and Contributors (the latter only
                // if the report is Draft and tagged with the user ID of the Contributor).
                $can_edit = $is_editor || ($is_contributor && $report->draft && $contributed);

                if ($can_edit)
                {
                    $menuitems[] = array(
                        'href' => get_permalink($report, 'edit'),
                        'rel' => 'nofollow',
                        'text' => 'Edit'
                    );
                }

                if ($report->draft && $is_editor)
                {
                    $menuitems[]    = array('href' => 'javascript:void(0);',
                                            'onclick' => 'confirm_publish(\''.get_permalink($report, 'publish').'\');',
                                            'rel' => 'nofollow',
                                            'text' => 'Publish');
                }

                if (!$report->draft && $is_admin)
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
        $db = new db_credentials();

        $user_name = get_logged_in_username();

        $users_table = new Users($db);
        $user = $users_table->get_user($user_name);

        // TODO(Contributors): only do the below bit if the user is a Contributor
        $contributors_table = new Contributors($db);

        $contributions = $contributors_table->get_contributed_reports($user);

        echo '<div class="reports_table">';
        echo   '<table class="sortable">';

        show_summary_table_header();

        foreach ($reports as $report)
        {
            $contributed = is_contributed_report($report, $contributions); // Did this user contribute this report?

            show_summary_table_item($report, $contributed);
        }

        echo   '</table>';
        echo '</div>';
    }

?>
