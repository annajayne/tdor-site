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


    echo '<h2>Blogposts</h2>';
    echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

    //TODO: command links (Current Entries | Archives | RSS)

    echo '<p>&nbsp;</p>';

    show_posts($posts);

?>