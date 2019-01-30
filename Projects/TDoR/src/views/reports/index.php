<?php
    /**
     * "Reports" page.
     *
     */


    /**
     * Get the HTML code for a <select> element for the "period" combobox.
     *
     * The options available include the given TDoR years and an option for a custom date range.
     *
     * @param int $first_year               The first TDoR year.
     * @param int $last_year                The last TDoR year.
     * @param string $selection             The selection.
     * @return string                       The HTML text of the <select> element.
     */
    function get_year_combobox_code($first_year, $last_year, $selection)
    {
        $code ='<select id="tdor_period" name="TDoR Period" onchange="onselchange_tdor_year();" >';

        for ($year = $first_year; $year <= $last_year; ++$year)
        {
            $label = 'TDoR '.$year.' (1 Oct '.($year - 1).' - 30 Sep '.$year. ')';

            $code .= get_combobox_option_code($year, $label, ($selection === $year) ? true : false);
        }

        $custom = 'custom';

        $code .= get_combobox_option_code($custom, 'Custom dates', ($selection === $custom) ? true : false);

        $code .= '</select>';

        return $code;
    }



    /**
     * Get the HTML code for a <select> element for the "country" combobox.
     *
     * The options available include all countries for which we have data in the database.
     *
     * @param string $selection             The selection.
     * @param array $countries              An array containing the country names and counts to add to the combobox.
     * @return string                       The HTML text of the <select> element.
     */
    function get_country_combobox_code($selection, $countries)
    {
        $country_names_only = array_keys($countries);
        $total_reports      = array_sum(array_values($countries) );

        if (!empty($selection) && ($selection != 'all') && !in_array($selection, $country_names_only) )
        {
            $country_names_only[] = htmlspecialchars($selection, ENT_QUOTES);

            sort($country_names_only);
        }

        $code ='<select id="country" name="Country" onchange="go();" >';

        $all_text =  'All ('.$total_reports.' '.get_report_count_caption($total_reports).')';

        $code .= get_combobox_option_code('all', $all_text, ($selection === 'all') ? true : false);

        foreach ($country_names_only as $country)
        {
            $count  = !empty($countries[$country]) ? $countries[$country] : 0;
            $text   = $country.' ('.$count.' '.get_report_count_caption($count).')';

            $code   .= get_combobox_option_code($country, $text,           ($selection === $country)      ? true : false);
        }

        $code .= '</select>';

        return $code;
    }


    /**
     * Show a command menu fo the page.
     *
     */
    function show_menu_links_for_reports($params)
    {
        $base_url       = ENABLE_FRIENDLY_URLS ? '/reports?' : '/?category=reports&';

        if (!empty($params->date_from_str) && !empty($params->date_to_str) )
        {
            $base_url  .= "from=$params->date_from_str&";
            $base_url  .= "to=$params->date_to_str&";
        }

        $base_url      .= "country=$params->country&";
        $base_url      .= "filter=$params->filter&";

        $menuitems[]    = array( 'href' => $base_url.'action=export&sortby=date&sortup=1',
                                 'rel' => 'nofollow',
                                 'text' => 'Export');

        $menuitems[]    = array('href' => $base_url.'action=slideshow',
                                 'target' => '_blank',
                                 'rel' => 'nofollow',
                                 'text' => 'Slideshow');

        $menuitems[]    = array('href' => $base_url.'action=memorial_card&sortup=1',
                                 'rel' => 'nofollow',
                                 'target' => '_blank',
                                 'text' => 'Memorial Cards');

        $menuitems[]    = array('href' => $base_url.'action=get_tweet_text&sortup=1',
                                 'rel' => 'nofollow',
                                 'text' => 'Get Tweet Text');

        if (is_logged_in() )
        {
            $menuitems[] = array('href' => $base_url.'action=add',
                                 'rel' => 'nofollow',
                                 'text' => 'Add');
        }

        if (!empty($menuitems) )
        {
            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).' | ';
            }

            // Trim trailing delimiter
            $menu_html = substr($menu_html, 0, strlen($menu_html) - 2);

            echo '<div class="command_menu">[ '.$menu_html.']</div>';
        }
    }
?>


<!-- Script -->
<script>
    function get_tdor_year_selection()
    {
        var ctrl = document.getElementById("tdor_period");

        return ctrl.options[ctrl.selectedIndex].value;
    }


    function get_country_selection()
    {
        var ctrl = document.getElementById("country");

        return ctrl.options[ctrl.selectedIndex].value;
    }


    function get_view_as_selection()
    {
        var ctrl = document.getElementById("view_as");

        return ctrl.options[ctrl.selectedIndex].value;
    }


    function get_filter_text()
    {
        var ctrl = document.getElementById("filter");

        return ctrl.value;
    }


    function get_url(from_date, to_date, country, view_as, filter)
    {
      <?php
        $url = ENABLE_FRIENDLY_URLS ? '/reports?' : '/index.php?category=reports&action=index&';
        echo "var url = '$url'";
      ?>

        url += 'from=' + from_date + '&to=' + to_date;
        url += '&country=' + country;
        url += '&view=' + view_as;
        url += '&filter=' + filter;

        return url;
    }


    function onselchange_tdor_year()
    {
        var year = get_tdor_year_selection();

        if ($.isNumeric(year) )
        {
            // NB no need to hide date pickers here as PHP deals with that for us once the page reloads.
            from_date   = (year - 1) + '-10-01';
            to_date     = year + '-09-30';

            set_session_cookie('reports_date_from', from_date);
            set_session_cookie('reports_date_to', to_date);

            var url = get_url(from_date, to_date, get_country_selection(), get_view_as_selection(), get_filter_text() );

            window.location.href = url;
        }
        else
        {
            // Show the date picker div
            var ctrl = document.getElementById("datepickers");

            ctrl.style = "display:inline;";
        }
    }


    function date_to_iso(date_str)
    {
        var d = new Date(date_str);

        return d.getFullYear() + '-' +
                   ('0'+ (d.getMonth() + 1) ).slice(-2) + '-' +
                   ('0'+ d.getDate() ).slice(-2);
        return n;
    }


    function go()
    {
        var from_date   = $('#datepicker_from').val();
        var to_date     = $('#datepicker_to').val();

        var country     = get_country_selection();
        var view_as     = get_view_as_selection();
        var filter      = get_filter_text();

        set_session_cookie('reports_country', country);
        set_session_cookie('reports_view_as', view_as);
        set_session_cookie('reports_filter', filter);

        if (from_date != '' && to_date != '')
        {
            set_session_cookie('reports_date_from', from_date);
            set_session_cookie('reports_date_to', to_date);

            var url = get_url(date_to_iso(from_date), date_to_iso(to_date), country, view_as, filter);

            window.location.href = url;
        }
        else
        {
            alert("Please select both start and end dates");
        }
    }


    $(document).ready(function()
    {
        $.datepicker.setDefaults(
        {
            dateFormat: 'dd M yy'
        });

        $(function()
        {
            $("#datepicker_from").datepicker();
            $("#datepicker_to").datepicker();
        });

        $('#apply_range').click(function()
        {
            go();
        });

        $('#apply_filter').click(function()
        {
            go();
        });

    });
</script>


<?php
    $report_count = count($params->reports);

    if ($params->reports_available)
    {
        $tdor_first_year                = get_tdor_year(new DateTime($params->report_date_range[0]) );
        $tdor_last_year                 = get_tdor_year(new DateTime($params->report_date_range[1]) );

        $selected_year                  = $tdor_last_year;
        $display_date_pickers           = '';

        if (!empty($params->date_from_str) && !empty($params->date_to_str) )
        {
            if (str_ends_with($params->date_from_str, '-10-01') && str_ends_with($params->date_to_str, '-09-30') )
            {
                $selected_year          = get_tdor_year(new DateTime($params->date_from_str) );
                $display_date_pickers   = 'none';
            }
            else
            {
                $selected_year          = 'custom';
                $display_date_pickers   = 'inline';
            }
        }

        $countries = Reports::get_countries_with_counts($params->date_from_str, $params->date_to_str, $params->filter);


        echo '<div class="nonprinting">';
        echo   '<div class="grid_12">TDoR period:<br />'.get_year_combobox_code($tdor_first_year, $tdor_last_year, $selected_year).'</div>';

        echo   '<div id="datepickers" style="display:'.$display_date_pickers.';">';
        echo     '<div class="grid_6">From Date:<br /><input type="text" name="datepicker_from" id="datepicker_from" class="form-control" placeholder="From Date" value="'.date_str_to_display_date($params->date_from_str).'" /></div>';
        echo     '<div class="grid_6">To Date:<br /><input type="text" name="datepicker_to" id="datepicker_to" class="form-control" placeholder="To Date" value="'.date_str_to_display_date($params->date_to_str).'" /> <input type="button" name="apply_range" id="apply_range" value="Apply" class="btn btn-success" /></div>';
        echo   '</div>';

        echo   '<div class="grid_12">Country:<br />'.get_country_combobox_code($params->country, $countries).'</div>';

        echo   '<div class="grid_6">View as:<br />'.get_view_combobox_code($params->view_as, 'onchange="go();"').'</div>';

        echo   '<div class="grid_6">Filter:<br /><input type="text" name="filter" id="filter" value="'.$params->filter.'" /> <input type="button" name="apply_filter" id="apply_filter" value="Apply" class="btn btn-success" /></div>';

        echo   '<hr>';
        echo '</div>';

        echo "<b>$report_count reports found</b>";

        show_menu_links_for_reports($params);

        echo '<br><br><br>';
    }

    if ($report_count > 0)
    {
        switch ($params->view_as)
        {
            case 'list':
                show_summary_table($params->reports);

                $url                = get_url();

                $newline            = '%0A';
                $tweet_text         = "Remembering our Dead - remembering trans people lost to violence and suicide.$newline";
                $tweet_text        .= $report_count.' reports from '.date_str_to_display_date($params->date_from_str).' to '.date_str_to_display_date($params->date_to_str).'.';

                if (!empty($params->filter) )
                {
                    $tweet_text    .= " (filtered by: $params->filter)";
                }

                $tweet_text        .= $newline.$newline.rawurlencode($url);

                show_social_links($url, $tweet_text);
                break;

            case 'thumbnails':
                show_thumbnails($params->reports);
                break;

            case 'map':
                show_summary_map($params->reports);
                break;

            case 'details':
                show_details($params->reports);
                break;
        }
    }
    else
    {
        echo '<br>No entries';
    }

?>