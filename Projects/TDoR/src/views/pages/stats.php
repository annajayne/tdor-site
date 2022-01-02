<?php
    /**
     * Statistics page.
     *
     */

    require_once('util/datetime_utils.php');                    // For date_str_to_display_date()
    require_once('models/reports.php');
    require_once('stats_table_view_impl.php');



    /**
     * Return the total number of reports for each year.
     *
     * @param Reports $reports_table            The reports table
     * @return array                            An array associated the report count for each year with the corresponding label
     */
    function get_year_report_counts($reports_table)
    {
        $query_params                   = new ReportsQueryParams();

        $query_params->status           = (is_editor_user() || is_admin_user() ) ? ReportStatus::draft | ReportStatus::published : ReportStatus::published;

        $report_counts                  = $reports_table->get_years_with_counts($query_params);

        krsort($report_counts);         // Sort into reverse order - most recent year first

        // If we are partway through the year, extrapolate what we know so far to a predicted total for the entire year
        $current_year                   = (int)date('Y');
        $is_leap_year                   = (boolean)date('L');
        $days_in_year                   = $is_leap_year ? 366 : 365;
        $current_day                    = (date('z') + 1);

        if ($current_day < $days_in_year)
        {
            if (isset($report_counts[$current_year]) )
            {
                $current_year_count                         = $report_counts[$current_year]['total'];

                $current_year_predicted_count               = (int)($days_in_year * ($current_year_count / $current_day) );

                $report_counts[$current_year]['predicted']  = $current_year_predicted_count;
            }
        }
        return $report_counts;
    }


    /**
     * Return the total number of reports for each TDoR period.
     *
     * @param Reports $reports_table            The reports table
     * @return array                            An array associated the report count for each TDoR period with the corresponding label
     */
    function get_tdor_period_report_counts($reports_table)
    {
        $report_counts                  = array();

        $report_date_range              = $reports_table->get_date_range();

        $first_year                     = get_tdor_year(new DateTime($report_date_range[0]) );
        $last_year                      = get_tdor_year(new DateTime($report_date_range[1]) );

        $query_params                   = new ReportsQueryParams();

        $query_params->date_from        = $report_date_range[0];
        $query_params->date_to          = $report_date_range[1];
        $query_params->status           = (is_editor_user() || is_admin_user() ) ? ReportStatus::draft | ReportStatus::published : ReportStatus::published;

        $tdor_year_started              = 1999;

        if ($first_year < $tdor_year_started)
        {
            $tdor_year_before_started   = $tdor_year_started - 1;

            $first_year                 = $tdor_year_started;

            $query_params->date_from    = '1901-01-01';
            $query_params->date_to      = $tdor_year_before_started.'-09-30';

            $report_count               = $reports_table->get_categories_with_counts($query_params);

            $item_title                 = get_item_title_html("TDoR $tdor_year_before_started and earlier",  "/reports?from=$query_params->date_from&to=$query_params->date_to");

            $report_counts[$item_title] = $report_count;
        }

        $current_date = date('Y-m-d');

        for ($year = $first_year; $year <= $last_year; ++$year)
        {
            $query_params->date_from    = strval($year - 1).'-10-01';
            $query_params->date_to      = $year.'-09-30';

            $year_report_count          = $reports_table->get_categories_with_counts($query_params);

            $item_label_suffix          = '<br>('.date_str_to_display_date($query_params->date_from).' - '. date_str_to_display_date($query_params->date_to).')';

            $item_title                 = get_item_title_html("TDoR $year", "/reports/tdor$year", $item_label_suffix);

            if ( ($current_date > $query_params->date_from) && ($current_date < $query_params->date_to) )
            {
                $datetime_from                  = new DateTime($query_params->date_from);
                $datetime_to                    = new DateTime($query_params->date_to);

                $days_in_period                 = $datetime_to->diff($datetime_from)->format('%a') + 1;

                $datetime__now                  = new DateTime();
                $current_day_in_period          = $datetime__now->diff($datetime_from)->format('%a') + 1;

                $current_period_count           = $year_report_count['total'];

                $current_period_predicted_count = (int)($days_in_period * ($current_period_count / $current_day_in_period) );

                $year_report_count['predicted'] = $current_period_predicted_count;
            }

            $report_counts[$item_title] = $year_report_count;
        }

        $report_counts = array_reverse($report_counts, true);         // Most recent year first

        return $report_counts;
    }


    /**
     * Return the total number of reports for each country.
     *
     * @param Reports $reports_table            The reports table
     * @return array                            An array associated the report count for each country with the corresponding label
     */
    function get_country_report_counts($reports_table)
    {
        $query_params                   = new ReportsQueryParams();

        $query_params->status           = (is_editor_user() || is_admin_user() ) ? ReportStatus::draft | ReportStatus::published : ReportStatus::published;

        $report_counts                  = $reports_table->get_countries_with_counts($query_params);

        arsort($report_counts, true);

        return $report_counts;
    }


    /**
     * Return the total number of reports for each category.
     *
     * @param Reports $reports_table            The reports table
     * @return array                            An array associated the report count for each category with the corresponding label
     */
    function get_category_report_counts($reports_table)
    {
        $query_params                   = new ReportsQueryParams();

        $query_params->status           = (is_editor_user() || is_admin_user() ) ? ReportStatus::draft | ReportStatus::published : ReportStatus::published;

        $report_counts                  = $reports_table->get_categories_with_counts($query_params);

        arsort($report_counts, true);

        return $report_counts;
    }



    $db                         = new db_credentials();
    $reports_table              = new Reports($db);

    $year_report_counts         = get_year_report_counts($reports_table);
    $tdor_period_report_counts  = get_tdor_period_report_counts($reports_table);
    $country_report_counts      = get_country_report_counts($reports_table);
    $category_report_counts     = get_category_report_counts($reports_table);

    echo '<p>&nbsp;</p>';
    echo '<h2>Statistics</h2>';

    echo '<div id="accordion">';

    echo   '<h3>Total Reports by Year</h3>';
    echo   '<div>';
    show_years_table($year_report_counts);
    echo     '<br>';
    echo   '</div>';

    echo   '<h3>Total Reports by TDoR period</h3>';
    echo   '<div>';
    show_tdor_periods_table($tdor_period_report_counts);
    echo     '<br>';
    echo   '</div>';

    echo   '<h3>Total Reports by Country</h3>';
    echo   '<div>';
    show_country_counts_table($country_report_counts);
    echo     '<br>';
    echo   '</div>';

    echo   '<h3>Total Reports by Category</h3>';
    echo   '<div>';
    show_category_counts_table($category_report_counts);
    echo     '<br>';
    echo   '</div>';

    echo '</div>';

?>

<script>
  $( function() {
      $("#accordion").accordion({
          heightStyle: "content",
          autoHeight: false,
          clearStyle: true,
          collapsible: true,
          active: false
      });
  } );
</script>
