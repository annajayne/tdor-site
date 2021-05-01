<?php
    /**
     * Support functions for blogpost pages.
     *
     */
    require_once('util/markdown_utils.php');            // For get_image_filenames_from_markdown()


    /**
     * Add the host to blogpost image links.
     *
     * We use this as StackEdit needs image links to include the host.
     *
     * @param string $markdown              The markdown to check.
     * @return string                       The updated markdown.
     */
    function add_host_to_image_links($markdown)
    {
        $host = get_host();

        $referenced_media_filenames = get_image_filenames_from_markdown($markdown);

        foreach ($referenced_media_filenames as $referenced_media_filename)
        {
            $components = parse_url($referenced_media_filename);

            if (!isset($components['scheme']) )
            {
                $markdown  = str_replace($referenced_media_filename, $host.'/'.$referenced_media_filename, $markdown);
                $markdown  = str_replace($host.'//', $host.'/', $markdown);
            }
        }
        return $markdown;
    }


    /**
     * Strip the host (if it's the current one) from image links.
     *
     * We use this as StackEdit needs image links to include the host, and we need to strip it off before storage.
     *
     * @param string $markdown              The markdown to check.
     * @return string                       The updated markdown.
     */
    function strip_host_from_image_links($markdown)
    {
        $host = get_host();

        $referenced_media_filenames = get_image_filenames_from_markdown($markdown);

        foreach ($referenced_media_filenames as $referenced_media_filename)
        {
            if (str_begins_with($referenced_media_filename, $host) )
            {
                $markdown  = str_replace($host.'//', $host.'/', $markdown);
                $markdown  = str_replace($host, '', $markdown);
            }
        }
        return $markdown;
    }


    /**
     * Get the HTML code for the top level command menu .
     *
     * @return string                   HTML code for the top level command menu.
     */
    function get_top_level_menu_html()
    {
        $menu_html = '';

        $menuitems[] = array('href' => '/blog',
                             'text' => 'Blog');

        if (is_admin_user() )
        {
            $menuitems[] = array('href' => '/blog?action=add',
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
     * @param Blogpost $blogpost        The given blogpost.
     * @return string                   HTML code for the corresponding command menu.
     *
     */
    function get_post_menu_html($blogpost)
    {
        $menu_html = '';

        if (is_admin_user() )
        {
            $menuitems[] = array('href' => ($blogpost->permalink.'?action=edit'),
                                 'rel' => 'nofollow',
                                 'text' => 'Edit');

            $menuitems[] = array('href' => 'javascript:void(0);',
                                 'onclick' => 'confirm_delete_post(\''.$blogpost->permalink.'?action=delete'.'\');',
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
     * Show the given blogpost.
     *
     * @param Blogpost $blogpost        The blogpost to display.
     */
    function show_blogpost($blogpost)
    {
        $datetime                       = new DateTime($blogpost->timestamp);

        $post_date                      = $datetime->format('l jS F, Y');
        $post_time                      = $datetime->format('g:ia');

        // Identify any relative links to images and replace them with site relative ones.
        $image_pathnames                = get_image_filenames_from_markdown($blogpost->content);

        // Convert the markdown in the description field to HTML
        $content                        = markdown_to_html($blogpost->content);

        $menu_html                      = get_post_menu_html($blogpost);

        $title_suffix                   = $blogpost->draft ? '<span class="command_menu_inline">[Draft]</span> ' : '';
        $title_suffix                  .= $blogpost->deleted ? '<span class="command_menu_inline">[Deleted]</span> ' : $menu_html;

        echo "<hr/>\n";
        echo "<p class='blogpost_heading'><a name='$blogpost->uid'></a><a href='$blogpost->permalink'>$blogpost->title</a> $title_suffix</p>\n";
        echo "<p class='blogpost_date'>$post_date</p><br />\n";

        echo "$content\n";
        echo '<p class="blogpost_author">';
        echo   "Posted by $blogpost->author at $post_time | <a href='$blogpost->permalink'>Get Link</a><br />";

        if (is_admin_user() )
        {
            echo "UID: $blogpost->uid<br />";
        }
        echo "</p>\n";
    }



?>