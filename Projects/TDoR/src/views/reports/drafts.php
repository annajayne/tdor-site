<?php
    /**
     * "Draft Reports" page.
     *
     */

    require_once('display_utils.php');


    echo '<h2>Draft Reports</h2>';
    echo '<p>&nbsp;</p>';

    echo '<div class="nonprinting">';
	echo   '<div class="grid_6">View as:<br />'.get_view_combobox_code($params->view_as, 'onchange="go();"').'</div>';
	echo   '<div class="grid_6">Filter:<br /><input type="text" name="filter" id="filter" value="'.$params->filter.'" /> <input type="button" name="apply_filter" id="apply_filter" value="Apply" class="btn btn-success" /></div>';
	echo   '<hr>';
	echo '</div>';

    $db                             = new db_credentials();
    $reports_table                  = new Reports($db);

    $query_params                   = new ReportsQueryParams();

    $query_params->status           = ReportStatus::draft;
    $query_params->filter           = $params->filter;

    $reports                        = $reports_table->get_all($query_params);

    $report_count                   = count($reports);

    if ($report_count > 0)
    {
        echo '<div class="nonprinting">';

        echo "<b>$report_count reports found</b>";
        echo '<br><br><br>';

        switch ($params->view_as)
        {
            case 'list':
                show_summary_table($reports);
                break;

            case 'thumbnails':
                show_thumbnails($reports);
                break;

            case 'map':
                show_summary_map($reports);
                break;

            case 'details':
                show_details($reports);
                break;
        }
    }
    else
    {
        echo '<br>No entries';
    }

    echo '<script src="/js/draft_reports.js"></script>';
?>
