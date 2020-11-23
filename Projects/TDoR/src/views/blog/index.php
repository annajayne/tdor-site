<?php
    /**
     * Blogpost index page.
     *
     */

    require_once('util/blog_utils.php');


    /**
     * Show the given blogposts.
     *
     * @param   Array   $blogposts      The blogposts to display.
     */
    function show_blogposts($blogposts)
    {
        $show_hidden_blogposts = is_admin_user();

        foreach ($blogposts as $blogpost)
        {
            if ($show_hidden_blogposts || (!$blogpost->draft && !$blogpost->deleted) )
            {
                show_blogpost($blogpost);
            }
        }
    }

    echo '<script src="/js/blog_editing.js"></script>';

    echo '<h2>Blog</h2>';
    echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

    echo '<p>&nbsp;</p>';
    echo get_top_level_menu_html();         // Top level menu
    echo '<p>&nbsp;</p>';

    show_blogposts($blogposts);

?>