<?php
    /**
     * Show a report.
     *
     */


    /**
     * Get a breadcrumb link representing the specified date.
     *
     * @param int $year                     The year.
     * @param int $month                    The month.
     * @param int $day                      The day.
     * @return string                       The corresponding URL, e.g. '/reports/year/month/day'
     */
    function get_breadcrumb_link_for_date($year, $month, $day)
    {
        $url = '';
        if (ENABLE_FRIENDLY_URLS)
        {
            $url = "/reports/$year";

            if ($month > 0)
            {
                $url .= "/$month";
            }
            if ($day > 0)
            {
                $url .= "/$day";
            }
        }
        return $url;
    }


    /**
     * Make an HTML link given a URL and the text to display.
     *
     * TODO: this is generic. It shouldn't be here.
     *
     * @param string $url                   The URL.
     * @param string $text                  The text.
     * @return string                       The generated HTML link.
     */
    function make_breadcrumb_link($url, $text)
    {
        return "<a href='$url'>$text</a>";
    }


    /**
     * Make a breadcrumb for the given report.
     *
     * @param Report $report                The report.
     * @return string                       HTML text for the breadcrumb.
     */
    function make_breadcrumb($report)
    {
        $breadcrumb = '';

        if (ENABLE_FRIENDLY_URLS)
        {
            $date           = new DateTime($report->date);

            $tdor_year      = get_tdor_year($date);

            $date_from_str  = ($tdor_year - 1).'-10-01';
            $date_to_str    = $tdor_year.'-09-30';

            $tdor_url       = "/reports?from=$date_from_str&to=$date_to_str";

            $year           = $date->format('Y');
            $month          = $date->format('m');
            $day            = $date->format('d');

            $year_url       = get_breadcrumb_link_for_date($year, 0, 0);
            $month_url      = get_breadcrumb_link_for_date($year, $month, 0);
            $day_url        = get_breadcrumb_link_for_date($year, $month, $day);

            $separator      = ' / ';

            $breadcrumb     = make_breadcrumb_link($tdor_url, "TDoR $tdor_year").$separator.
                                make_breadcrumb_link($year_url, $year).$separator.
                                make_breadcrumb_link($month_url, $date->format('F') ).$separator.
                                make_breadcrumb_link($day_url, $day).$separator.
                                '<b>'.$report->name.'</b>';
        }
        return $breadcrumb;
    }


    $breadcrumb = make_breadcrumb($report);
    if (!empty($breadcrumb) )
    {
        echo $breadcrumb.'<br><br>';
    }

    show_report($report);
?>
