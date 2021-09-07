<?php
    /**
     * Blog RSS feed page
     *
     */
    require_once('util/path_utils.php');                // For append_path()
    require_once('util/markdown_utils.php');            // For markdown_to_html()
    require_once('models/blog_table.php');

    $host                           = raw_get_host();

    $blog_title                     = 'Remembering Our Dead - Blog';
    $blog_desc                      = 'Remembering trans people lost to violence or suicide';
    $blog_url                       = append_path($host, '/blog');

    $allowedTags                    = '<p><span><a><br><b><strong><h1><h2><h3><h4><i><em><ul><img><table><tr><th><td><div><blockquote><li><ol><pre><code>';


    // Include *all* blogposts
    $db                             = new db_credentials();
    $blog_table                     = new BlogTable($db);
    $query_params                   = new BlogTableQueryParams();

    $blogposts                      = $blog_table->get_all($query_params);

    $newline                        = "\r\n";

    $timezone                       = new DateTimeZone('UTC');

    header("Content-Type: text/xml");
    header("Pragma: no-cache");

    echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>".$newline;
    echo "<rss version=\"0.91\">".$newline;

    echo '<channel>'.$newline;
    echo   "<title>$blog_title</title>".$newline;
    echo   "<link>$blog_url</link>".$newline;
    echo   "<description>$blog_desc</description>".$newline;
    echo   '<language>en-us</language>'.$newline;

    foreach ($blogposts as $blogpost)
    {
        $blogpost_datetime          = new DateTime($blogpost->timestamp, $timezone);

        // Get rid of any html tags in the title and content of the entry so we don't pass any unwanted ones through to the RSS feed
        $item_title                 = strip_tags($blogpost->title, '<i>');

        // Content
        $item_desc                  = markdown_to_html($blogpost->content);

        // Strip unwanted tags and entities
        $item_desc                  = strip_tags($item_desc, $allowedTags);
        $item_desc                  = str_replace('&nbsp;', ' ', $item_desc);

        // Replace single HTML tags with XHTML equivalents
        $item_desc                  = preg_replace("/<img(.*)>/sU", "<img\\1 />", $item_desc);
        $item_desc                  = str_replace(" / />", " />", $item_desc);

        $item_link                  = append_path($host, $blogpost->permalink);

        $item_pubdate               = $blogpost_datetime->format("r");

        echo '<item>'.$newline;
        echo   "<title>$item_title</title>".$newline;
        echo   "<link>$item_link</link>".$newline;
        echo   "<description><![CDATA[".$item_desc."]]></description>".$newline;
        echo   "<pubDate>$item_pubdate</pubDate>".$newline;
        echo '</item>'.$newline;
    }

    echo   '</channel>'.$newline;
    echo '</rss>'.$newline;
?>