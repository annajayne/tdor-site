<?php
    /**
     * Statistics page.
     *
     */

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
        $report_counts = $reports_table->get_years_with_counts();

        krsort($report_counts);         // Sort into reverse order - most recent year first

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

        $report_count                   = $reports_table->get_categories_with_counts($query_params);
        $report_count['total']          = array_sum(array_values($report_count) );

        $tdor_year_started              = 1999;

        if ($first_year < $tdor_year_started)
        {
            $tdor_year_before_started   = $tdor_year_started - 1;

            $first_year                 = $tdor_year_started;

            $query_params->date_from    = '1901-01-01';
            $query_params->date_to      = $tdor_year_before_started.'-09-30';

            $report_count               = $reports_table->get_count($query_params);

            $item_title                 = get_item_title_html("TDoR $tdor_year_before_started and earlier",  "/reports?from=$start_date&to=$end_date");

            $report_counts[$item_title] = $report_count;
        }

        for ($year = $first_year; $year <= $last_year; ++$year)
        {
            $query_params->date_from    = strval($year - 1).'-10-01';
            $query_params->date_to      = $year.'-09-30';

            $year_report_count          = $reports_table->get_count($query_params);

            $item_label_suffix          = '<br>('.date_str_to_display_date($query_params->date_from).' - '. date_str_to_display_date($query_params->date_to).')';

            $item_title                 = get_item_title_html("TDoR $year", "/reports/tdor$year", $item_label_suffix);

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
        $report_counts = $reports_table->get_countries_with_counts();

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
        $report_counts = $reports_table->get_categories_with_counts();

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
