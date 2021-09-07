<?php
    /**
     * Blogpost page.
     *
     */
    require_once('views/blog/blog_view.php');


    echo '<script src="/js/blog_editing.js"></script>';

    // Top level menu
    echo BlogView::get_top_level_menu_html();

    echo '<p>&nbsp;</p>';

    $view = new BlogView();
    $view->show_blogpost($blogpost);

?>
