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

        $tdor_year_started = 1999;

        if ($first_year < $tdor_year_started)
        {
            $tdor_year_before_started = strval($tdor_year_started - 1);

            $label = "TDoR $tdor_year_before_started and earlier";

            $code .= get_combobox_option_code($tdor_year_before_started, $label, ($selection === $tdor_year_before_started) ? true : false);

            $first_year = 1999;
        }

        for ($year = $first_year; $year <= $last_year; ++$year)
        {
            $label = 'TDoR '.$year.' (1 Oct '.($year - 1).' - 30 Sep '.$year. ')';

            $code .= get_combobox_option_code($year, $label, ($selection === strval($year) ) ? true : false);
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
     * Get the HTML code for a <select> element for the "categories" combobox.
     *
     * The options available include all categories for which we have data in the database.
     *
     * @param string $selection             The selection.
     * @param array $categories             An array containing the category names and counts to add to the combobox.
     * @return string                       The HTML text of the <select> element.
     */
    function get_category_combobox_code($selection, $categories)
    {
        $category_names_only  = array_keys($categories);
        $total_reports        = array_sum(array_values($categories) );

        if (!empty($selection) && ($selection != 'all') && !in_array($selection, $category_names_only) )
        {
            $category_names_only[] = htmlspecialchars($selection, ENT_QUOTES);

            sort($category_names_only);
        }

        $code ='<select id="category" name="category" onchange="go();" >';

        $all_text =  'All ('.$total_reports.' '.get_report_count_caption($total_reports).')';

        $code .= get_combobox_option_code('all', $all_text, ($selection === 'all') ? true : false);

        foreach ($category_names_only as $category)
        {
            $count  = !empty($categories[$category]) ? $categories[$category] : 0;
            $text   = $category.' ('.$count.' '.get_report_count_caption($count).')';

            $code   .= get_combobox_option_code($category, $text,           ($selection === $category)      ? true : false);
        }

        $code .= '</select>';

        return $code;
    }


    /**
     * Show a command menu for the page.
     *
     */
    function show_menu_links_for_reports($params)
    {
        $is_bot         = is_bot(get_user_agent() );

        $base_url       = ENABLE_FRIENDLY_URLS ? '/reports?' : '/?controller=reports&';

        if (!empty($params->date_from_str) && !empty($params->date_to_str) )
        {
            $base_url  .= "from=$params->date_from_str&";
            $base_url  .= "to=$params->date_to_str&";
        }

        $base_url      .= "country=$params->country&";
        $base_url      .= "filter=$params->filter&";

        $menuitems[]    = array(    'href' => $base_url.'action=slideshow',
                                    'target' => '_blank',
                                    'rel' => 'nofollow',
                                    'text' => 'Slideshow');

        $menuitems[]    = array(    'href' => $base_url.'action=memorial_card&sortup=1',
                                    'rel' => 'nofollow',
                                    'target' => '_blank',
                                    'text' => 'Memorial Cards');

        if (!$is_bot)
        {
            $menuitems[]    = array('href' => $base_url.'action=export&sortby=date&sortup=1',
                                    'rel' => 'nofollow',
                                    'text' => 'Download Data');

            $menuitems[]    = array('href' => $base_url.'action=get_tweet_text&sortup=1',
                                    'rel' => 'nofollow',
                                    'text' => 'Download Tweets');
        }

        if (is_editor_user() )
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

            $rss_feed_url   = $base_url.'action=rss';

            $rss_link_html  = "<a href='$rss_feed_url' target='_blank' rel='nofollow' alt='RSS'><img src='/images/rss.svg' class='reports_page_rss_button' /></a>";

            echo "<div class='command_menu nonprinting'>$menu_html $rss_link_html</div>";
        }
    }


    $report_count = count($params->reports);

    if ($params->reports_available)
    {
        $db                             = new db_credentials();
        $reports_table                  = new Reports($db);

        $tdor_first_year                = get_tdor_year(new DateTime($params->report_date_range[0]) );
        $tdor_last_year                 = get_tdor_year(new DateTime($params->report_date_range[1]) );

        $selected_year                  = strval($tdor_last_year);
        $display_date_pickers           = '';

        if (!empty($params->date_from_str) && !empty($params->date_to_str) )
        {
            if (str_ends_with($params->date_from_str, '-10-01') && str_ends_with($params->date_to_str, '-09-30') )
            {
                $selected_year_from         = strval(get_tdor_year(new DateTime($params->date_from_str) ) );
                $selected_year_to           = strval(get_tdor_year(new DateTime($params->date_to_str) ) );

                if ($selected_year_from == $selected_year_to)
                {
                    $selected_year          = $selected_year_to;
                    $display_date_pickers   = 'none';
                }
                else
                {
                    $selected_year          = 'custom';
                    $display_date_pickers   = 'inline';
                }
            }
            else if ( ($params->date_from_str === '1901-01-01') && ($params->date_to_str === '1998-09-30') )
            {
                $selected_year          = '1998';
                $display_date_pickers   = 'none';
            }
            else
            {
                $selected_year          = 'custom';
                $display_date_pickers   = 'inline';
            }
        }

        $query_params               = new ReportsQueryParams();

        $query_params->date_from    = $params->date_from_str;
        $query_params->date_to      = $params->date_to_str;
        $query_params->filter       = $params->filter;

        $countries                  = $reports_table->get_countries_with_counts($query_params);

        $query_params->country      = $params->country;

        $categories                 = $reports_table->get_categories_with_counts($query_params);

        echo '<div class="nonprinting">';
        echo   '<div class="grid_12">TDoR period:<br />'.get_year_combobox_code($tdor_first_year, $tdor_last_year, $selected_year).'</div>';

        echo   '<div id="datepickers" style="display:'.$display_date_pickers.';">';
        echo     '<div class="grid_6">From Date:<br /><input type="text" name="datepicker_from" id="datepicker_from" class="form-control" placeholder="From Date" value="'.date_str_to_display_date($params->date_from_str).'" /></div>';
        echo     '<div class="grid_6">To Date:<br /><input type="text" name="datepicker_to" id="datepicker_to" class="form-control" placeholder="To Date" value="'.date_str_to_display_date($params->date_to_str).'" /> <input type="button" name="apply_range" id="apply_range" value="Apply" class="btn btn-success" /></div>';
        echo   '</div>';

        echo   '<div class="grid_6">Country:<br />'.get_country_combobox_code($params->country, $countries).'</div>';

        echo   '<div class="grid_6">Category:<br />'.get_category_combobox_code($params->category, $categories).'</div>';

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

    echo '<script src="/js/reports.js"></script>';

?>