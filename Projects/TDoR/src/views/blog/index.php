<?php
    /**
     * Blogpost index page.
     *
     */
    require_once('views/blog/blog_view.php');                   // For BlogView


    echo '<script src="/js/blog_editing.js"></script>';

    echo '<h2>Blog</h2>';
    echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

    echo '<p>&nbsp;</p>';
    echo BlogView::get_top_level_menu_html();         // Top level menu
    echo '<p>&nbsp;</p>';

    $view = new BlogView();

    $view->show_thumbnails              = true;
    $view->default_thumbnail_filename   = '/images/blog/default_blogpost_thumbnail.jpg';

    $view->show_blogpost_summaries($blogposts);
?>