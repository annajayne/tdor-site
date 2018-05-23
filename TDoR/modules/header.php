<?php

    class page_metadata
    {
        public  $site_name;
        public  $twitter_account;
        public  $title;
        public  $description;
        public  $url;
        public  $image;
    }


    function echo_meta_property($name, $value)
    {
        echo "<meta property='$name' content='$value' />\n";
    }


    function get_current_report_item_id()
    {
        //TODO: adjust for friendly URLs
        require_once('models/report.php');

        if ( ($id == 0) && isset($_GET['uid']) )
        {
            $uid = $_GET['uid'];

            $id = Report::find_id_from_uid($uid);
        }

        if ( ($id == 0) && isset($_GET['id']) )
        {
            $id = $_GET['id'];
        }

        return $id;
    }


    function get_metadata($controller, $action)
    {
        $host     = (isset($_SERVER['HTTPS']) ? 'https' : 'http')."://$_SERVER[HTTP_HOST]";
        $uri      = $_SERVER[REQUEST_URI];

        $metadata = new page_metadata();

        $metadata->site_name        = 'Remembering Our Dead';
        $metadata->twitter_account  = '@annajayne';
        $metadata->description      = 'This site gives details of trans people known to have been lost to violence, and is intended as a supporting resource for Transgender Day of Remembrance (TDoR) events.';
        $metadata->url              = $host.$uri;
        $metadata->image            = "$host/images/tdor_candle_jars.jpg";

        switch ($action)
        {
            case 'search':  $metadata->title = 'Search';    break;
            case 'about':   $metadata->title = 'About';     break;
            case 'index':   $metadata->title = 'Reports';   break;
            default:                                        break;
        }

        $id = 0;
        $uid = '';

        if ( ($controller === 'reports') && ($action === 'show') )
        {
            $id = get_current_report_item_id();
            if ($id > 0)
            {
                $item = Report::find($id);

                $date = get_display_date($item);
                $location = "$item->location, $item->country";

                $desc = $item->name;
                if ($item->age !== '')
                {
                    $desc .= " was $item->age and";
                }

                $desc .= ' '.get_displayed_cause_of_death($item);

                $desc .= " in $location.";

                $metadata->title        = "$item->name ($date)";
                $metadata->description  = $desc;
                $metadata->image        = $host.get_photo_pathname($item->photo_filename);
            }
        }
        return $metadata;
    }


    $metadata   = get_metadata($controller, $action);

    $page_title = !empty($metadata->title) ? "$metadata->site_name - $metadata->title" : $metadata->site_name;
    $page_desc  = empty($metadata->description) ? $page_title : $metadata->description;


    echo "<title>$page_title</title>\n";
    echo "<meta name='description' content='$page_desc'>\n";

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