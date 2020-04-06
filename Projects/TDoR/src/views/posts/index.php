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
        foreach ($posts as $post)
        {
            show_post($post);
        }
    }

    echo '<script src="/js/blog_editing.js"></script>';

    echo '<h2>Blogposts</h2>';
    echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

    echo '<p>&nbsp;</p>';
    echo get_top_level_menu_html();         // Top level menu
    echo '<p>&nbsp;</p>';

    show_posts($posts);

?>