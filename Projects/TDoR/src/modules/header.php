<?php
    /**
     *  Header properties for an HTML page.
     */


    /**
     *  Link preview metadata for a page.
     */
    class page_metadata
    {
        /** @var string                     The name of the site. */
        public  $site_name;

        /** @var string                     The handle of the associated Twitter account. */
        public  $twitter_account;

        /** @var string                     The title of the page. */
        public  $title;

        /** @var string                     The keywords for the page. */
        public  $keywords;

        /** @var string                     A description of the page. */
        public  $description;

        /** @var string                     The URL of the page. */
        public  $url;

        /** @var string                     The canonical URL of the page. */
        public  $canonical_url;

        /** @var string                     The URL of the associated link preview image, if any. */
        public  $image;
    }


    /**
     * Echo an HTML meta property.
     *
     * @param string @name                  The name of the property.
     * @param string @value                 The value of the property.
     */
    function echo_meta_property($name, $value)
    {
        echo "<meta property='$name' content='$value' />\n";
    }


    /**
     *  Get the id of the report to display from the current URL.
     *
     *  The id may be encoded as either an id (integer) or uid (hex string).
     *
     *  TODO: address the overlap between this function and ReportsController::get_current_id().
     *
     *  @return int                   The id of the report to display.
     */
    function get_current_report_id()
    {
        $id     = 0;
        $uid    = '';

        require_once('models/reports.php');

        $db             = new db_credentials();
        $reports_table  = new Reports($db);

        if (isset($_GET['uid']) )
        {
            $uid = $_GET['uid'];
        }

        if ( ($id == 0) && isset($_GET['id']) )
        {
            $id = $_GET['id'];
        }

        if (empty($uid) && ENABLE_FRIENDLY_URLS)
        {
            $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
            $uid = get_uid_from_friendly_url($path);
        }

        if ( ($id == 0) && !empty($uid) )
        {
            $id = $reports_table->find_id_from_uid($uid);
        }
        return $id;
    }


    /**
     *  Get report metadata including the detailed title.
     *
     * @param page_metadata $metadata       Generic page metadata to act as a starting point.
     * @return page_metadata                Detailed metadata.
     */
    function get_reports_metadata($metadata)
    {
        $date_from_str  = get_cookie(DATE_FROM_COOKIE, '');
        $date_to_str    = get_cookie(DATE_TO_COOKIE, '');
        $country        = get_cookie(COUNTRY_COOKIE, '');
        $category       = get_cookie(CATEGORY_COOKIE, '');
        $filter         = get_cookie(FILTER_COOKIE, '');
        $view           = get_cookie(VIEW_AS_COOKIE, '');

        if (ENABLE_FRIENDLY_URLS)
        {
            $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)

            $range = get_date_range_from_url($path);

            if (count($range) === 2)
            {
                if (!empty($range[0]) && !empty($range[1]) )
                {
                    $date_from_str  = $range[0];
                    $date_to_str    = $range[1];
                }
            }
        }

        if (isset($_GET['from']) && isset($_GET['to']) )
        {
            $date_from_str  = date_str_to_iso($_GET['from']);
            $date_to_str    = date_str_to_iso($_GET['to']);
        }

        if (isset($_GET['country']) )
        {
            $country         = $_GET['country'];
        }

        if (isset($_GET['category']) )
        {
            $category         = $_GET['category'];
        }

        if (isset($_GET['filter']) )
        {
            $filter         = $_GET['filter'];
        }

        if (isset($_GET['view']) )
        {
            $view         = $_GET['view'];
        }

        if (!empty($date_from_str) && !empty($date_to_str) )
        {
            require_once('models/reports.php');

            $page_canonical_url = '';

            if ($view != 'list')
            {
                $page_canonical_url  = get_host().(ENABLE_FRIENDLY_URLS ? '/reports?' : '/index.php?controller=reports&action=index&');
                $page_canonical_url .= 'from='.date_str_to_iso($date_from_str).'&to='.date_str_to_iso($date_to_str);
                $page_canonical_url .= '&country='.urlencode($country);
                $page_canonical_url .= '&category='.urlencode($category);
                $page_canonical_url .= '&filter='.urlencode($filter);
                $page_canonical_url .= "&view=list";
            }

            if (str_ends_with($date_from_str, '-10-01') && str_ends_with($date_to_str, '-09-30') )
            {
                $tdor_year = get_tdor_year(new DateTime($date_from_str) );

                $metadata->title .= " - TDoR $tdor_year";
            }

            $date_from_str          = date_str_to_display_date($date_from_str);
            $date_to_str            = date_str_to_display_date($date_to_str);

            $qualifiers             = "$date_from_str to $date_to_str";

            if (!empty($filter) )
            {
                $qualifiers .= "; filtered by '$filter'";
            }

            $metadata->description   = $qualifiers;
            $metadata->canonical_url = $page_canonical_url;
        }
        return $metadata;
    }


    /**
     *  Get page metadata for the given controller and action.
     *
     *  @param string $controller           The controller ('pages' or 'reports').
     *  @param string $action               The action.
     *  @return page_metadata               The metadata for the page.
     */
    function get_metadata($controller, $action)
    {
        $host                       = get_host();
        $metadata                   = new page_metadata();

        $metadata->site_name        = 'Remembering Our Dead';
        $metadata->twitter_account  = '@TDoRinfo';
        $metadata->title            = get_page_title($controller, $action);         // See Controller::get_page_title()
        $metadata->keywords         = get_page_keywords($controller, $action);      // See Controller::get_page_keywords()
        $metadata->description      = 'This site memorialises trans people who have passed away, as a supporting resource for the Trans Day of Remembrance (TDoR).';
        $metadata->url              = get_url();
        $metadata->image            = $host.'/images/tdor_candle_jars.jpg';

        if ( ($controller == 'reports') && ($action == 'index') )
        {
            $metadata                   = get_reports_metadata($metadata);
        }

        $id = 0;
        $uid = '';

        if ( ($controller === 'reports') && ($action === 'show') )
        {
            $id = get_current_report_id();
            if ($id > 0)
            {
                $db                     = new db_credentials();
                $reports_table          = new Reports($db);

                $report                 = $reports_table->find($id);

                $summary_text           = get_summary_text($report);

                $metadata->title        = $summary_text['title'];
                $metadata->description  = $summary_text['desc'].'.';
                $metadata->image        = $host.get_thumbnail_pathname($report->photo_filename);
            }
        }
        return $metadata;
    }

    $metadata           = get_metadata($controller, $action);

    $page_title         = !empty($metadata->title) ? "$metadata->site_name - $metadata->title" : $metadata->site_name;
    $page_desc          = empty($metadata->description) ? $page_title : $metadata->description;
    $page_keywords      = htmlspecialchars($metadata->keywords, ENT_QUOTES);

    $page_title         = htmlspecialchars($page_title, ENT_QUOTES);
    $page_desc          = htmlspecialchars($page_desc, ENT_QUOTES);

    echo "<title>$page_title</title>\n";
    echo "<meta name='description' content='$page_desc'>\n";

    if (!empty($page_keywords) )
    {
        echo "<meta name='keywords' content='$page_keywords'>\n";
    }

    if (!empty($metadata->canonical_url) )
    {
        echo "<link rel='canonical' href='$metadata->canonical_url'/>\n";
    }

    // Facebook meta properties
    echo_meta_property('og:type',               'website');
    echo_meta_property('og:site_name',          $metadata->site_name);
    echo_meta_property('og:title',              $page_title);

    echo_meta_property('og:description',        $page_desc);
    echo_meta_property('og:url',                $metadata->url);
    echo_meta_property('og:image',              $metadata->image);

    // Twitter meta properties
    echo_meta_property('twitter:card',          'summary_large_image');
    echo_meta_property('twitter:site',          $metadata->twitter_account);
    echo_meta_property('twitter:title',         $page_title);
    echo_meta_property('twitter:description',   $page_desc);
    echo_meta_property('twitter:image',         $metadata->image);

?>