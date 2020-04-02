<?php
    /**
     * Delete the current Blogpost.
     *
     */

    //require_once('models/blog_utils.php');


    //if (is_editor_user() )
    {
        $db             = new db_credentials();
        $posts_table    = new Posts($db);

        if ($posts_table->delete($post) )
        {
            echo "Blogpost $post->title deleted";
        }
    }

?>
