<?php
    /**
     * Administrative command for viewing/administering the blog.
     *
     */


    function show_blogposts_table($blogposts)
    {
        $host = get_host();

        $blogpost_count = get_blogpost_counts($blogposts);

        echo '<p>&nbsp;</p>';

        echo   '<table class="sortable" style="overflow-x:auto; font-size: 0.8em;" cellpadding="5" border="1">';
        echo     '<thead>';
        echo       '<tr>';
        echo         '<th>Title</th>';
        echo         '<th>Author</th>';
        echo         '<th>Timestamp</th>';
        echo         '<th>Published</th>';
        echo         '<th>Draft</th>';
        echo         '<th>Deleted</th>';
        echo         '<th>UID</th>';
        echo        '<th>Permalink</th>';
        echo         '<th>Actions</th>';
        echo       '</tr>';
        echo     '</thead>';

        echo     '<tbody>';

        $yes    = '<b>yes</b>';
        $no     = 'no';

        foreach ($blogposts as $blogpost)
        {
            $timestamp          = new DateTime($blogpost->timestamp);
            $display_timestamp  = $timestamp->format('j M Y H:i:s');

            $published          = (!$blogpost->draft && !$blogpost->deleted) ? $yes : $no;
            $draft              = $blogpost->draft ? $yes : $no;
            $deleted            = $blogpost->deleted ? $yes : $no;

            $menuitems          = array();

            $menuitems[]        = array('href' => $blogpost->permalink.'?action=edit',
                                        'rel' => 'nofollow',
                                        'text' => 'Edit');

            if (!$blogpost->deleted)
            {
                $prompt = 'Delete this blogpost?';

                $menuitems[]        = array('href' => 'javascript:void(0);',
                                            'onclick' => "confirm_delete('$prompt', '$blogpost->permalink?action=delete');",
                                            'rel' => 'nofollow',
                                            'text' => 'Delete');
            }
            else
            {
                $menuitems[]        = array('href' => $blogpost->permalink.'?action=undelete',
                                            'rel' => 'nofollow',
                                            'text' => 'Undelete');
            }

            $menu_html = '';

            foreach ($menuitems as $menuitem)
            {
                $menu_html .= get_link_html($menuitem).' | ';
            }

            // Trim trailing delimiter
            $menu_html = substr($menu_html, 0, strlen($menu_html) - 2);

            echo '<tr align="center">';
            echo    "<td><b>$blogpost->title</a></td>";
            echo    "<td>$blogpost->author</td>";
            echo    "<td>$display_timestamp</td>";
            echo    "<td>$published</td>";
            echo    "<td>$draft</td>";
            echo    "<td>$deleted</td>";
            echo    "<td>$blogpost->uid</td>";
            echo    "<td><a href='$blogpost->permalink' target='_blank'>$host$blogpost->permalink</a></td>";
            echo    '<td align="center" class="nonprinting">['.$menu_html.']</td>';
            echo '</tr>';
        }
        echo   '</tbody>';

        echo   '<tfoot>';
        echo     '<tr align="center">';
        echo      '<th><i>Totals:</i></th>';
        echo      '<th></th>';
        echo      '<th></th>';
        echo      '<th><i>'.$blogpost_count['published'].'</i></th>';
        echo      '<th><i>'.$blogpost_count['draft'].'</i></th>';
        echo      '<th><i>'.$blogpost_count['deleted'].'</i></th>';
        echo      '<th><i>'.$blogpost_count['total'].'</i></th>';
        echo      '<th></th>';
        echo      '<th></th>';
        echo     '</tr>';
        echo   '</tfoot>';

        echo '</table>';
    }


    function get_blogpost_counts($blogposts)
    {
        $blogpost_count = array();

        $blogpost_count['total'] = count($blogposts);

        $blogpost_count['draft'] = 0;
        $blogpost_count['published'] = 0;
        $blogpost_count['deleted'] = 0;

        foreach ($blogposts as $blogpost)
        {
            if ($blogpost->deleted)
            {
                ++$blogpost_count['deleted'];
            }
            else if ($blogpost->draft)
            {
                ++$blogpost_count['draft'];
            }
            else
            {
                ++$blogpost_count['published'];
            }
        }
        return $blogpost_count;
    }


    function administer_blog()
    {
        $db             = new db_credentials();
        $blog_table    = new BlogPosts($db);

        $blogposts      = $blog_table->get_all();

        $blogpost_count = get_blogpost_counts($blogposts);

        echo '<br><h2>Administer Blog</h2><br>';

        show_blogposts_table($blogposts);

        echo '<p>&nbsp;</p>';
    }

?>
