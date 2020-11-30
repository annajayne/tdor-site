<?php
    /**
     * Statistics page implementation functions
     *
     */



    /**
     * Implementation function to get the HTML code for the header of the first column.
     *
     * The table will have 2 columns: "<Item Name>" and "Total Reports", with the latter including a horizontal bar indigating the magnitude.
     *
     * @param string $text                  The text of the header
     * @param string $link_url              The URL to link $text to. Optional.
     * @param string $text_after_link       Text to include after the link. Optional, and used only if $link_url is set.
     * @return string                       The corresponding header title or link.
     */
    function get_item_title_html($text, $link_url = '', $text_after_link = '')
    {
        $html = $text;

        if (!empty($link_url) )
        {
            $html = "<a href='$link_url'>$text</a> $text_after_link";
        }
        return $html;
    }


    /**
     * Show a generic table giving the total reports for a set of items (countries, periods etc.).
     *
     * The table will have 2 columns: "<Item Name>" and "Total Reports", with the latter including a horizontal bar indigating the magnitude.
     *
     * @param array $column0_header         The properties of the first column header.
     * @param array $item_report_counts     The data to be displayed.
     */
    function show_item_counts_table($column0_header, $item_report_counts, $total_report_count = 0)
    {
        $header_title   = $column0_header['text'];
        $header_align   = isset($column0_header['align']) ? "align='".$column0_header['align']."'" : '';
        $header_width   = isset($column0_header['width']) ? "width='".$column0_header['width']."'" : '';

        $item_names     = array_keys($item_report_counts);

        $highest_count  = max($item_report_counts);

        echo '<p>&nbsp;</p>';
        echo '<table class="sortable" border="1" rules="all" style="border-color: #666;" cellpadding="10" width="100%">';

        echo '<thead>';
        echo   '<tr>';
        echo     "<th $header_align $header_width>$header_title</th>";
        echo     '<th align="center" colspan="2">Total Reports</th>';
        echo   '</tr>';
        echo '</thead>';

        echo '<tbody>';

        foreach ($item_names as $item_name)
        {
            $count      = $item_report_counts[$item_name];

            $percentage = 100 * ($count / $highest_count);

            echo '<tr>';
            echo   "<td>$item_name</td>";
            echo   "<td align='left' width='5em' style='border-right:none;'>$count</td>";

            echo   "<td align='left' style='border-left:none;'>";
            if ($percentage > 0.25)
            {
                echo "<div style='background-color:darkred; width:$percentage%;'>&nbsp;</div>";
            }
            echo   '</td>';
            echo '</tr>';
        }
        echo '</tbody>';

        echo '</table>';
    }


    /**
     * Show a table giving the total reports for each TDoR period.
     *
     * The table will have 2 columns: "TDoR period" and "Total Reports", with the latter including a horizontal bar indigating the magnitude.
     *
     * @param array $tdor_period_report_counts      An array associated the report count for each TDoR Period with the corresponding label
     */
    function show_tdor_periods_table($tdor_period_report_counts)
    {
        $column0_header = array('align' => 'left',
                                'width' => '30%',
                                'text' => 'TDoR Period');

        show_item_counts_table($column0_header, $tdor_period_report_counts);
    }


    /**
     * Show a table giving the total reports for each country.
     *
     * The table will have 2 columns: "Country" and "Total Reports", with the latter including a horizontal bar indigating the magnitude.
     *
     * @param array $country_report_counts          An array associated the report count for each country with the corresponding label
     */
    function show_country_counts_table($country_report_counts)
    {
        $column0_header = array('align' => 'left',
                                'width' => '10%',
                                'text' => 'Country');

        show_item_counts_table($column0_header, $country_report_counts);
    }


    /**
     * Show a table giving the total reports in each category.
     *
     * The table will have 2 columns: "Category" and "Total Reports", with the latter including a horizontal bar indigating the magnitude.
     *
     * @param array $category_report_counts          An array associated the report count for each category with the corresponding label
     */
    function show_category_counts_table($category_report_counts)
    {
        $column0_header = array('align' => 'left',
                                'width' => '10%',
                                'text' => 'Category');

        show_item_counts_table($column0_header, $category_report_counts);
    }



?>
