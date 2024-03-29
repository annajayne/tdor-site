<?php
    require_once('defines.php');
    require_once('util/misc.php');
    require_once('util/utils.php');
    require_once('util/sitemap_generator.php');
    require_once('models/connection.php');
    require_once('models/db_utils.php');
    require_once('models/reports.php');
    require_once('models/blog_table.php');
    require_once('views/display_utils.php');
    require_once('controllers/controller.php');
    require_once('controllers/reports_controller.php');


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
                                'pages/statistics',
                                'pages/api',
                                'pages/about',
                                'blog',
                                'reports?view=list');

    // Reports pages for each year
    for ($year = $first_year; $year < $last_year; ++$year)
    {
        $urls[] = 'reports/'.$year.'?view=list';
    }

    foreach ($urls as $url)
    {
        $gen->add($host.'/'.ltrim($url, '/') );
    }

    $db             = new db_credentials();
    $reports_table  = new Reports($db);

    $reports        = $reports_table->get_all();

    // ...individual report pages for each victim
    foreach ($reports as $report)
    {
        $date_created = !empty($report->date_created) ? $report->date_created : date("Y-m-d H:i:s");
        $date_updated = !empty($report->date_updated) ? $report->date_updated : $date_created;

        $gen->add($host.'/'.ltrim(get_permalink($report), '/'), $date_updated);
    }

    $blog_table     = new BlogTable($db);

    $blog_params    = new BlogTableQueryParams();
    $blogposts      = $blog_table->get_all($blog_params);

    // ...and finally: individual blogposts
    foreach ($blogposts as $blogpost)
    {
        $date_updated = !empty($blogpost->timestamp) ? str_before($blogpost->timestamp, ' ') : date("Y-m-d");

        $gen->add($host.'/'.ltrim($blogpost->permalink, '/'), $date_updated);
    }

// Generate the sitemap
    $gen->generate();

?>