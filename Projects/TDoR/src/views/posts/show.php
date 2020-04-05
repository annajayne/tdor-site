<?php 
    /**
     * Blogpost page.
     *
     */

    require_once('util/blog_utils.php');


    // Top level menu
    echo get_top_level_menu_html();

    echo '<p>&nbsp;</p>';

    show_post($post);

?>
