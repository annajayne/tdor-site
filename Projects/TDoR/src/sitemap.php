<?php
    require_once('utils.php');
    require_once('misc.php');
    require_once('connection.php');
    require_once('models/reports.php');
    require_once('display_utils.php');
    require_once('controllers/reports_controller.php');
    require_once('util/sitemap_generator.php');


    $host               = get_host();
    
    // Retrieve data on the report(s) we have
    $controller         = new ReportsController();
    $params             = $controller->get_current_params();

    $first_date         = new DateTime($params->report_date_range[0]);
    $last_date          = new DateTime($params->report_date_range[1]);

    $first_year         = (int)$first_date->format('Y');
    $last_year          = (int)$last_date->format('Y');

    // The sitemap generator
    $gen                = new SitemapGenerator();

    // Build an array of urls to include, starting with the static pages
    $urls               = array('',                                 // The blank is the homepage
                                'pages/search',
                                'pages/about',
                                'pages/reports?view=list');

    // Reports pages for each year
    for ($year = $first_year; $year < $last_year; ++$year)
    {
        $urls[] = 'pages/reports/'.$year.'?view=list';
    }

    foreach ($urls as $url)
    {
        $gen->add($host.'/'.ltrim($url, '/') );
    }

    $reports = Reports::get_all();

    // ...and finally: individual report pages for each victim
    foreach ($reports as $report)
    {
        $date_created = !empty($report->date_created) ? $report->date_created : date("Y-m-d");
        $date_updated = !empty($report->date_updated) ? $report->date_updated : $date_created;

        $gen->add($host.'/'.ltrim(get_permalink($report), '/'), $date_updated);
    }

    // Generate the sitemap
    $gen->generate();

?>