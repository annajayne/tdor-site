<?php
    /**
     * Support functions for blogpost pages.
     *
     */
    require_once('lib/parsedown/Parsedown.php');                // https://github.com/erusev/parsedown
    require_once('lib/parsedown/ParsedownExtra.php');           // https://github.com/erusev/parsedown-extra
    require_once('lib/parsedown/ParsedownExtraPlugin.php');     // https://github.com/tovic/parsedown-extra-plugin#automatic-relnofollow-attribute-on-external-links


    /**
     * Show the given post.
     *
     * @param Post      $post               The post to display.
     */
    function show_post($post)
    {
        $date = new DateTime($post->timestamp);

        $post_date = $date->format('l dS F, Y');
        $post_time = $date->format('H:i:s');

        // Use Parsedown (and specifically the ParsedownExtraPlugIn) to convert the markdown in the description field to HTML
        // Note that external links should have target=_blank and rel=nofollow attributes, and the markdown may contain embedded HTML for embedded video (YouTube, Vimeo etc.).
        $parsedown = new ParsedownExtraPlugin();

        $parsedown->links_attr = array();
        $parsedown->links_external_attr = array('rel' => 'nofollow', 'target' => '_blank');

        $content = $parsedown->text($post->content); 


        echo '<hr/>';
        echo "<p class='blogpost_heading'><a name='$post->id'></a><a href='$post->permalink'>$post->title</a>\n";
        echo "<p class='blogpost_date'>$post_date</p><br />\n";

        echo "<p>$content</p>\n";
        echo "<p class='blogpost_author'>Posted by $post->author at $post_time | <a href='$post->permalink'>Get Link</a><br />UID: $post->uid<br /></p>\n";
    }


?>