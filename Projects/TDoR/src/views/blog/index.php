<?php
    /**
     * Blogpost index page.
     *
     */

    require_once('util/blog_utils.php');



    function get_first_n_words($longtext, $wordcount)
    {
        // remove redundant Windows CR
        $longtext = preg_replace ("/\r/", "", $longtext);

        // A space to an end, just in case
        $longtext = $longtext . " ";

        //  Regular expression for a word
        $wordpattern = "([\w\(\)\.,;?!-_«»\"\'’]*[ \n]*)";

        // Determine how many words are in the text
        $maxwords = preg_match_all ("/" . $wordpattern . "/", $longtext, $words);

        //  Make sure that the maximum number of available words is matched
        $wordcount = min($wordcount, $maxwords);

        // Create a regular expression for the desired number of words
        $pattern = "/" . $wordpattern . "{0," . $wordcount . "}/";

        // Read the desired number of words
        $match = preg_match ($pattern, $longtext, $shorttext);

        // Get the right result out of the result array
        $shorttext = $shorttext[0];

        return $shorttext;
    }


    /**
     * Get a subtitle for the given blogpost (as this isn't a DB property yet, we create one from the content)
     *
     * @param Blogpost   $blogpost      The blogposts for which a subtitle should be returned.
     * @return string                   The subtitle.
     */
    function get_blogpost_subtitle($blogpost)
    {
        $parsedown                      = new ParsedownExtraPlugin();

        $parsedown->links_attr          = array();
        $parsedown->links_external_attr = array('rel' => 'nofollow', 'target' => '_blank');

        $content                        = $parsedown->text($blogpost->content);

        $subtitle                       = str_replace("<br />", " ", $content);
        $subtitle                       = strip_tags($subtitle, "");
        $subtitle                       = get_first_n_words($subtitle, 40).'...';

        return $subtitle;
    }


    /**
     * Show a summary tile for the given blockpost
     *
     * @param Blogpost   $blogpost      The blogposts for which a summary tile should be shown.
     */
    function show_blogpost_summary($blogpost)
    {
        $title      = !empty($blogpost->title) ? $blogpost->title : '(untitled)';
        $subtitle   = get_blogpost_subtitle($blogpost);

        echo '<div class="grid_6">';
        echo   "<p><a href='$blogpost->permalink' title='$title'><b>$title</b></a></p>";
        echo   "<div align='center'><a href='$blogpost->permalink'><img src='$blogpost->thumbnail_filename' /></a></div>";
        echo   "<p><small>$subtitle</small></p>";
        echo '</div>';
    }


    /**
     * Show the given blogposts.
     *
     * @param   Array   $blogposts      The blogposts to display.
     */
    function show_blogposts($blogposts)
    {
        $show_hidden_blogposts = is_admin_user();

        foreach ($blogposts as $blogpost)
        {
            if ($show_hidden_blogposts || (!$blogpost->draft && !$blogpost->deleted) )
            {
                echo "\n";
                show_blogpost_summary($blogpost);
            }
        }
    }

    echo '<script src="/js/blog_editing.js"></script>';

    echo '<h2>Blog</h2>';
    echo '<div><img src="/images/tdor_candle_jars.jpg" /></div>';

    echo '<p>&nbsp;</p>';
    echo get_top_level_menu_html();         // Top level menu
    echo '<p>&nbsp;</p>';

    show_blogposts($blogposts);

?>