<?php
    /**
     * Administrative command to list any reports which require attenion (missing geolocation, truncated memorial card text etc.)
     *
     */

    require_once('models/reports.php');


    function show_issues_table_header()
    {
        $columns = array('Date', 'Name', 'Location', 'Country', 'Issues');

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
     * Show an item in the table for the given report.
     *
     * @param Report $report                The report to display details for.
     * @param string $issues                Details of any known issues the report has.
     */
    function show_issues_table_item($report, $issues)
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

        $is_admin           = is_admin_user();

        $site_config        = get_config();

        $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];

        $menuitems          = [];

        if (!$edits_disabled || $is_admin)
        {
            $menuitems[]        = array('href' => get_permalink($report, 'edit'),
                                        'rel' => 'nofollow',
                                        'text' => 'Edit');
        }

        $menu_html = '';

        foreach ($menuitems as $menuitem)
        {
            $menu_html .= get_link_html($menuitem).'<br>';
        }

        echo "<td style='white-space: nowrap;' sorttable_customkey='$report->date'>". date_str_to_display_date($report->date)."</td>";
        echo "<td><a href='".$link_url."'>".$report->name."</a>$qualifiers</td>";

        echo "<td>". htmlspecialchars($place, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>". htmlspecialchars($report->country, ENT_QUOTES, 'UTF-8')."</td>";
        echo "<td>$issues</td>";
        echo '<td align="center" class="nonprinting">'.$menu_html.'</td>';

        echo "</tr>";
    }


    /**
     * Display details of any reports with issues (memorial card text clipped, geolocation missing etc.)
     *
     */
    function display_report_issues()
    {
        $db                     		= new db_credentials();
        $reports_table          		= new Reports($db);

        $query_params           		= new ReportsQueryParams();
        $query_params->status   		= (is_editor_user() || is_admin_user() ) ? ReportStatus::draft | ReportStatus::published : ReportStatus::published;
        $query_params->sort_field		= 'date';
        $query_params->sort_ascending	= false;

        $reports                		= $reports_table->get_all($query_params);

        $reports_with_issues    		= array();

        foreach ($reports as $report)
        {
            $desc = get_short_description($report);

            $issues = '';

            if (!$report->draft && (empty($report->latitude) || empty($report->longitude)))
            {
                $issues .= ' Geolocation missing.';
            }

            if (str_ends_with($desc, '...'))
            {
                $issues .= ' Memorial text clipped.';
            }

            if (!empty($issues))
            {
                $reports_with_issues[] = array($report, $issues);
            }
        }

        echo count($reports_with_issues)." reports require attention<br><br>";

        if (!empty($reports_with_issues))
        {
            echo '<div class="grid12"><div class="reports_table">';
            echo '<table class="sortable">';

            show_issues_table_header();

            foreach ($reports_with_issues as $report_with_issues)
            {
                $report = $report_with_issues[0];
                $issues = $report_with_issues[1];

                show_issues_table_item($report,$issues);
            }

            echo '</table></div></div>';
        }
    }

?>
