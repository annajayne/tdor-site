<?php
    /**
     * Blogpost index page.
     *
     */

    require_once('util/blog_utils.php');


    /**
     * Show the given posts.
     *
     * @param   Array   $posts          The posts to display.
     */
    function show_posts($posts)
    {
        $show_drafts = is_admin_user();

        foreach ($posts as $post)
        {
            if (!$post->draft || $show_drafts)
            {
                show_post($post);
            }
        }
    }

    echo '<script src="/js/blog_editing.js"></script>';

    echo '<h2>Blog</h2>';
    echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

    echo '<p>&nbsp;</p>';
    echo get_top_level_menu_html();         // Top level menu
    echo '<p>&nbsp;</p>';

    show_posts($posts);

?>