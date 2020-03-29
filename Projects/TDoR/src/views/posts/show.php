<?php 
    /**
     * Blogpost page.
     *
     */

    require_once('util/blog_utils.php');

    echo '<div class="command_menu nonprinting">';
    echo   '<a href="/posts">Index</a>';
    echo '</div>';
    echo '<p>&nbsp;</p>';


    show_post($post);

?>
