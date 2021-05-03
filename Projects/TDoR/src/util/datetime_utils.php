<?php
    /**
     * Date/time utility functions.
     *
     */

    /**
     * Create an ISO date string for the given year, month and day.
     *
     * @param int $year                           The year,
     * @param int $month                          The month.
     * @param int $day                            The day.
     * @return string                             The corresponding ISO date string.
     */
    function make_iso_date($year, $month, $day)
    {
        return strval($year).'-'.sprintf("%02d", $month).'-'.sprintf("%02d", $day);
    }


    /**
     * Convert the given date string to an ISO date representation.
     *
     * @param string $date_str                    The date to parse (e.g. "27 Jul 2018"),
     * @return string                             The corresponding ISO date string.
     */
    function date_str_to_iso($date_str)
    {
        $date_components    = date_parse($date_str);

        $day                = $date_components['day'];
        $month              = $date_components['month'];
        $year               = $date_components['year'];

        return make_iso_date($year, $month, $day);
    }


    /**
     * Convert the given date string to a display representation of the form "dd MMM YYYY".
     *
     * @param string $date_str                    The date to parse.
     * @return string                             The corresponding display date.
     */
    function date_str_to_display_date($date_str)
    {
        if (!empty($date_str) )
        {
            $date = new DateTime($date_str);

            return $date->format('j M Y');
        }
        return '';
    }


    /**
     * Return the dates bounding the given year, month and day.
     *
     * @param int $year                           The year.
     * @param int $month                          The month.
     * @param int $day                            The day.
     * @return array                              An array containing the start and end dates bounding the given year, month and day, in ISO format.
     */
    function get_date_range_from_year_month_day($year, $month, $day)
    {
        $date_from_str = '';
        $date_to_str = '';

        if ($year > 0)
        {
            if ($month > 0)
            {
                if ($day > 0)
                {
                    $date_from_str  = make_iso_date($year, $month, $day);
                    $date_to_str    = make_iso_date($year, $month, $day);
                }
                else
                {
                    $date_from_str  = make_iso_date($year, $month, 1);
                    $date_to_str    = make_iso_date($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year) );
                }
            }
            else
            {
                $date_from_str      = make_iso_date($year, 1, 1);
                $date_to_str        = make_iso_date($year, 12, 31);
            }
        }
        return array($date_from_str, $date_to_str);
    }

?>