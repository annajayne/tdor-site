<?php
    /**
     * "Recent Updates" page.
     *
     */

    require_once('views/display_utils.php');

    $max_results = 100; // Default

    if (isset($_GET['max_results']) )
    {
        $max_results    = intval($_GET['max_results']);
    }

    echo '<h2>Recent Updates</h2>';
    echo '<p>&nbsp;</p>';

    echo '<div class="nonprinting">';
    echo   '<div class="grid_4">View as:<br />'.get_view_combobox_code($params->view_as, 'onchange="go();"').'</div>';
    echo   '<div class="grid_4">Filter:<br /><input type="text" name="filter" id="filter" value="'.$params->filter.'" /> <input type="button" name="apply_filter" id="apply_filter" value="Apply" class="btn btn-success" /></div>';
    echo   '<div class="grid_4">Show:<br /><input type="text" name="max_results" id="max_results" value="'.$max_results.'" /> <input type="button" name="apply_max_results" id="apply_max_results" value="Apply" class="btn btn-success" /></div>';
    echo   '<hr>';
    echo '</div>';

    $db                             = new db_credentials();
    $reports_table                  = new Reports($db);

    $query_params                   = new ReportsQueryParams();

    $query_params->status           = (is_editor_user() || is_admin_user() ) ? (ReportStatus::draft | ReportStatus::published) : ReportStatus::published;
    $query_params->filter           = $params->filter;
    $query_params->sort_field       = 'date_updated';
    $query_params->sort_ascending   = false;
    $query_params->max_results      = $max_results;

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

    echo '<script src="/js/recent_reports.js"></script>';
?>
