<?php
    /**
     * Delete the current Blogpost.
     *
     */
    require_once('util/blog_utils.php');


    if (is_admin_user() )
    {
        $db             = new db_credentials();
        $posts_table    = new BlogPosts($db);

        if ($posts_table->delete($post) )
        {
            echo '<h2>Blog</h2>';
            echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

            echo '<p>&nbsp;</p>';
            echo get_top_level_menu_html();         // Top level menu
            echo '<p>&nbsp;</p>';

            $post_type = $post->draft ? '[Draft]' : '';

            echo "Blogpost \"<b>$post->title</b>\" $post_type deleted";
        }
    }

?>
