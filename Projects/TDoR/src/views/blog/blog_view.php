<?php
    /**
     * Implementation class for blog views.
     *
     */
    require_once('util/path_utils.php');


    /**
     * Implementation class for blog views.
     *
     */
    class BlogView
    {
        /** @var boolean                 Whether thumbnails should be shown on blog index pages. */
        public  $show_thumbnails;

        /** @var string                  The default thumbanil to use for blogposts which don't have one. */
        public  $default_thumbnail_filename;


        /**
         * Constructor
         *
         */
        public function __construct()
        {
        }


        /**
         * Get the HTML code for the top level command menu .
         *
         * @return string                   HTML code for the top level command menu.
         */
        static function get_top_level_menu_html()
        {
            $menu_html = '';

            $menuitems[]        = array('href' => '/blog',
                                        'text' => 'Blogposts');

            if (is_admin_user() )
            {
                $menuitems[]    = array('href' => '/blog?action=add',
                                        'rel' => 'nofollow',
                                        'text' => 'Add Blogpost');

                $menuitems[]    = array('href' => '/pages/admin?target=blog',
                                        'rel' => 'nofollow',
                                        'text' => 'Administer Blog');
            }

            $rss_feed_url       ='/blog?action=rss';

            $svg_attributes     = "width='25px;' style='margin: 0px 0px 2px 0px; vertical-align:middle;'";

            $rss_link           = "<a href='$rss_feed_url' target='_blank'><img src='/images/rss.svg' alt='RSS' $svg_attributes /></a>";

            if (!empty($menuitems) )
            {
                $menu_html = '';

                foreach ($menuitems as $menuitem)
                {
                    $menu_html .= get_link_html($menuitem).' | ';
                }

                // Trim trailing delimiter
                $menu_html = substr($menu_html, 0, strlen($menu_html) - 2);

                $menu_html = '<div align="right"><span class="command_menu_inline nonprinting" style="line-height:25px;">&nbsp;&nbsp;[ '.$menu_html.']</span>&nbsp;&nbsp;'.$rss_link.'</div>';
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
        static function get_blogpost_menu_html($blogpost)
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
            $timezone                       = new DateTimeZone('UTC');

            $datetime                       = new DateTime($blogpost->timestamp, $timezone);

            $blogpost_date                  = $datetime->format('l jS F, Y');
            $blogpost_time                  = $datetime->format('g:ia');

            // Identify any relative links to images and replace them with site relative ones.
            $image_pathnames                = get_image_filenames_from_markdown($blogpost->content);

            // Convert the markdown in the description field to HTML
            $content                        = markdown_to_html($blogpost->content, 'lightbox[Blog]');

            $menu_html                      = self::get_blogpost_menu_html($blogpost);

            $title_suffix                   = $blogpost->draft ? '<span class="command_menu_inline">[Draft]</span> ' : '';
            $title_suffix                  .= $blogpost->deleted ? '<span class="command_menu_inline">[Deleted]</span> ' : $menu_html;

            echo "<hr/>\n";
            echo "<p class='blogpost_heading'><a name='$blogpost->uid'></a><a href='$blogpost->permalink'>$blogpost->title</a> $title_suffix</p>\n";
            echo "<p class='blogpost_date'>$blogpost_date</p><br />\n";

            echo "$content\n";
            echo '<p class="blogpost_author">';
            echo   "Posted by $blogpost->author at $blogpost_time | <a href='$blogpost->permalink'>Get Link</a><br />";

            if (is_admin_user() )
            {
                echo "UID: $blogpost->uid<br />";
            }
            echo "</p>\n";
        }


        /**
         * Show a summary tile for the given blockpost
         *
         * @param Blogpost  $blogpost                       The blogposts for which a summary tile should be shown.
         */
        function show_blogpost_summary($blogpost)
        {
            $title              = !empty($blogpost->title) ? $blogpost->title : '(untitled)';
            $subtitle           = $blogpost->get_subtitle();
            $thumbnail_filename = '';

            if ($this->show_thumbnails)
            {
                $thumbnail_filename = $blogpost->thumbnail_filename;

                if (!empty($thumbnail_filename) )
                {
                    if (is_path_relative($thumbnail_filename) )
                    {
                        // Relative path - prefix with '/blog/content/';
                        $thumbnail_filename = "/blog/content/$thumbnail_filename";
                    }
                }
                else
                {
                    // Default blogpost thumbnail
                    $thumbnail_filename = $this->default_thumbnail_filename;
                }

                $title_suffix   = $blogpost->draft ? '<span class="command_menu_inline"> [Draft]</span> ' : '';
                $title_suffix  .= $blogpost->deleted ? '<span class="command_menu_inline"> [Deleted]</span> ' : $menu_html;

                echo '<div class="grid_12">';
                echo   '<div class="grid_3" align="center">';
                echo     "<a href='$blogpost->permalink'><img src='$thumbnail_filename' /></a>";
                echo   '</div>';
                echo   '<div class="grid_9">';
                echo     "<p><a href='$blogpost->permalink' title='$title'><b>$title</b></a>$title_suffix</p>";
                echo     "<p><small>$subtitle</small></p>";
                echo   '</div>';
                echo '</div>';
            }
            else
            {
                echo '<div class="grid_12">';
                echo   "<p><a href='$blogpost->permalink' title='$title'><b>$title</b></a></p>";
                echo   "<p><small>$subtitle</small></p>";
                echo '</div>';
            }
        }


        /**
         * Show summaries of the given blogposts.
         *
         * @param   array   $blogposts                      The blogposts to display.
         */
        function show_blogpost_summaries($blogposts)
        {
            $show_hidden_blogposts = is_admin_user();

            foreach ($blogposts as $blogpost)
            {
                if ($show_hidden_blogposts || (!$blogpost->draft && !$blogpost->deleted) )
                {
                    echo "\n";
                    $this->show_blogpost_summary($blogpost);
                }
            }
        }

    }

?>
