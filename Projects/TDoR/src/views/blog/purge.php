<?php
    /**
     * Purge the current Blogpost.
     *
     */
    require_once('util/blog_utils.php');
    require_once('views/blog/blog_view.php');                   // For BlogView


    if (is_admin_user() )
    {
        $db             = new db_credentials();
        $blog_table     = new BlogTable($db);

        if ($blog_table->purge($blogpost) )
        {
            $blogpost->deleted = true;

            echo '<h2>Blog</h2>';
            echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

            echo '<p>&nbsp;</p>';
            echo BlogView::get_top_level_menu_html();         // Top level menu
            echo '<p>&nbsp;</p>';

            $blogpost_type = $blogpost->draft ? '[Draft]' : '';
            $blogpost_type .= $blogpost->deleted ? '[Deleted]' : '';

            echo "Blogpost \"<b>$blogpost->title</b>\" $blogpost_type purged";
        }
    }

?>
