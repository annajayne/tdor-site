<?php
    /**
     * Blogpost index page.
     *
     */

    require_once('util/blog_utils.php');



    /**
     * Get a subtitle for the given blogpost (as this isn't a DB property yet, we create one from the content)
     *
     * @param Blogpost   $blogpost                      The blogpost for which a subtitle should be returned.
     * @return string                                   The subtitle.
     */
    function get_blogpost_subtitle($blogpost)
    {
        $content    = markdown_to_html($blogpost->content);

        $subtitle   = str_replace("<br />", " ", $content);
        $subtitle   = strip_tags($subtitle, "");
        $subtitle   = get_first_n_words($subtitle, 40).'...';

        return $subtitle;
    }


    /**
     * Show a summary tile for the given blockpost
     *
     * @param Blogpost  $blogpost                       The blogposts for which a summary tile should be shown.
     * @param boolean   $show_thumbnail                 Whether thumbnails should be shown.
     * @param string    $default_thumbnail_filename     The image to use as a default thumbnail if none is specified by the blogpost.
     */
    function show_blogpost_summary($blogpost, $show_thumbnail, $default_thumbnail_filename)
    {
        $title              = !empty($blogpost->title) ? $blogpost->title : '(untitled)';
        $subtitle           = get_blogpost_subtitle($blogpost);
        $thumbnail_filename = '';

        if ($show_thumbnail)
        {
            $thumbnail_filename = $blogpost->thumbnail_filename;

            if (!empty($thumbnail_filename) )
		    {
                if (is_path_relative($thumbnail_filename) )
                {
                    // Relative path - prefix with '/blog/content/';
                    $thumbnail_filename = "/blog/content/$thumbnail_filename";
                }
		    }
            else
		    {
                // Default blogpost thumbnail
                $thumbnail_filename = $default_thumbnail_filename;
		    }

            $title_suffix                   = $blogpost->draft ? '<span class="command_menu_inline"> [Draft]</span> ' : '';
            $title_suffix                  .= $blogpost->deleted ? '<span class="command_menu_inline"> [Deleted]</span> ' : $menu_html;

            echo '<div class="grid_12">';
            echo   '<div class="grid_3" align="center">';
            echo     "<a href='$blogpost->permalink'><img src='$thumbnail_filename' /></a>";
            echo   '</div>';
            echo   '<div class="grid_9">';
            echo     "<p><a href='$blogpost->permalink' title='$title'><b>$title</b></a>$title_suffix</p>";
            echo     "<p><small>$subtitle</small></p>";
            echo   '</div>';
            echo '</div>';
        }
        else
        {
            echo '<div class="grid_12">';
            echo   "<p><a href='$blogpost->permalink' title='$title'><b>$title</b></a></p>";
            echo   "<p><small>$subtitle</small></p>";
            echo '</div>';
        }
	}


    /**
     * Show the given blogposts.
     *
     * @param   Array   $blogposts                      The blogposts to display.
     * @param boolean   $show_thumbnail                 Whether thumbnails should be shown.
     * @param string    $default_thumbnail_filename     The image to use as a default thumbnail if none is specified by a given blogpost.
     */
    function show_blogpost_summaries($blogposts, $show_thumbnail, $default_thumbnail_filename)
    {
        $show_hidden_blogposts = is_admin_user();

        foreach ($blogposts as $blogpost)
        {
            if ($show_hidden_blogposts || (!$blogpost->draft && !$blogpost->deleted) )
            {
                echo "\n";
                show_blogpost_summary($blogpost, $show_thumbnail, $default_thumbnail_filename);
            }
        }
    }


    /**
     * Show the given blogposts.
     *
     * @param   Array   $blogposts                      The blogposts to display.
     * @param boolean   $show_thumbnail                 Whether thumbnails should be shown.
     * @param string    $default_thumbnail_filename     The image to use as a default thumbnail if none is specified by a given blogpost.
     */
    function show_blogposts_by_month($blogposts, $show_thumbnail, $default_thumbnail_filename)
    {
        $show_hidden_blogposts = is_admin_user();

        $dates = [];

        foreach ($blogposts as $blogpost)
        {
            if ($show_hidden_blogposts || (!$blogpost->draft && !$blogpost->deleted) )
            {
                $datetime               = new DateTime($blogpost->timestamp);

                $month                  = $datetime->format('m');
                $year                   = $datetime->format('Y');

                $long_month_and_year    = $datetime->format('F Y');

                if (array_search($long_month_and_year, $dates) === false)
                {
                    // This is the first post from the given month/year we've displayed, so show a heading
                    $dates[] = $long_month_and_year;

                    echo "<br><h2>$long_month_and_year</h2>\n";
                }

                echo "\n<ul>";

                show_blogpost_summary($blogpost, $show_thumbnail, $default_thumbnail_filename);

                echo '</ul>';
            }
        }
    }


    echo '<script src="/js/blog_editing.js"></script>';

    echo '<h2>Blog</h2>';
    echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

    echo '<p>&nbsp;</p>';
    echo get_top_level_menu_html();         // Top level menu
    echo '<p>&nbsp;</p>';

    $view_as = 'summaries';
    if (isset($_GET['view']) )
    {
        $view_as = $_GET['view'];
    }

    $show_thumbnails = true;
    $default_thumbnail_filename = '/images/trans_flag.jpg';

    switch ($view_as)
    {
        case 'summaries':
        default:
            show_blogpost_summaries($blogposts, $show_thumbnails, $default_thumbnail_filename);
            break;

        case 'monthly';
            show_blogposts_by_month($blogposts, $show_thumbnails, $default_thumbnail_filename);
            break;
    }
?>