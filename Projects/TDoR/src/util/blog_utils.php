<?php
    /**
     * Support functions for blogpost pages.
     *
     */
    require_once('lib/parsedown/Parsedown.php');                // https://github.com/erusev/parsedown
    require_once('lib/parsedown/ParsedownExtra.php');           // https://github.com/erusev/parsedown-extra
    require_once('lib/parsedown/ParsedownExtraPlugin.php');     // https://github.com/tovic/parsedown-extra-plugin#automatic-relnofollow-attribute-on-external-links



/**
    * Get the HTML code for the top level command menu .
     *
     * @return string                   HTML code for the top level command menu.
     */
    function get_top_level_menu_html()
    {
        $menu_html = '';

        $menuitems[] = array('href' => '/posts',
                             'text' => 'Blogposts');

        if (is_admin_user() )
        {
            $menuitems[] = array('href' => '/posts?action=add',
                                 'rel' => 'nofollow',
                                 'text' => 'Add');
		}
        if (!empty($menuitems) )
        {
            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).' | ';
            }

            // Trim trailing delimiter
            $menu_html = substr($menu_html, 0, strlen($menu_html) - 2);

            $menu_html = '<div align="right"><span class="command_menu_inline nonprinting">&nbsp;&nbsp;[ '.$menu_html.']</span></div>';
        }
        return $menu_html;
    }


    /**
     * Get the HTML code for the command menu links for the given blogpost.
     *
     * @param Post $post                The given blogpost.
     * @return string                   HTML code for the corresponding command menu.
     *
     */
    function get_post_menu_html($post)
    {
        $menu_html = '';

        if (is_admin_user() )
        {
            $menuitems[] = array('href' => ($post->permalink.'?action=edit'),
                                 'rel' => 'nofollow',
                                 'text' => 'Edit');

            $menuitems[] = array('href' => 'javascript:void(0);',
                                 'onclick' => 'confirm_delete_post(\''.$post->permalink.'?action=delete'.'\');',
                                 'rel' => 'nofollow',
                                 'text' => 'Delete');

            if (!empty($menuitems) )
            {
                $menu_html = '';

                foreach ($menuitems as $menuitem)
                {
                    $menu_html .= get_link_html($menuitem).' | ';
                }

                // Trim trailing delimiter
                $menu_html = substr($menu_html, 0, strlen($menu_html) - 2);

                $menu_html = '<span class="command_menu_inline nonprinting">&nbsp;&nbsp;[ '.$menu_html.']</span>';
            }
        }
        return $menu_html;
    }


    /**
     * Show the given post.
     *
     * @param Post      $post           The post to display.
     */
    function show_post($post)
    {
        $datetime                       = new DateTime($post->timestamp);

        $post_date                      = $datetime->format('l jS F, Y');
        $post_time                      = $datetime->format('g:ia');

        // Use Parsedown (and specifically the ParsedownExtraPlugIn) to convert the markdown in the description field to HTML
        // Note that external links should have target=_blank and rel=nofollow attributes, and the markdown may contain embedded HTML for embedded video (YouTube, Vimeo etc.).
        $parsedown                      = new ParsedownExtraPlugin();

        $parsedown->links_attr          = array();
        $parsedown->links_external_attr = array('rel' => 'nofollow', 'target' => '_blank');

        $content                        = $parsedown->text($post->content); 

        $menu_html                      = get_post_menu_html($post);

        $title_suffix                   = $post->draft ? '<span class="command_menu_inline">[Draft]</span> ' : '';
        $title_suffix                  .= $post->deleted ? '<span class="command_menu_inline">[Deleted]</span> ' : $menu_html;

        echo "<hr/>\n";
        echo "<p class='blogpost_heading'><a name='$post->uid'></a><a href='$post->permalink'>$post->title</a> $title_suffix</p>\n";
        echo "<p class='blogpost_date'>$post_date</p><br />\n";

        echo "$content\n";
        echo '<p class="blogpost_author">';
        echo   "Posted by $post->author at $post_time | <a href='$post->permalink'>Get Link</a><br />";

        if (is_admin_user() )
        {
            echo "UID: $post->uid<br />";
        }
        echo "</p>\n";
    }


?>