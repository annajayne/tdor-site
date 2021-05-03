<?php
    /**
     * Administrative command for viewing/administering the blog.
     *
     */

    require_once('views/pages/admin/blog_import.php');



    function show_blogposts_table($blogposts)
    {
        $host           = get_host();
        $blogpost_count = get_blogpost_counts($blogposts);

        echo '<p>&nbsp;</p>';

        echo   '<table class="sortable" style="overflow-x:auto; font-size: 0.8em;" cellpadding="5" border="1">';
        echo     '<thead>';
        echo       '<tr>';
        echo         '<th>Title</th>';
        echo         '<th>Author</th>';
        echo         '<th>Timestamp (UTC)</th>';
        echo         '<th>Published</th>';
        echo         '<th>Draft</th>';
        echo         '<th>Deleted</th>';
        echo         '<th>UID</th>';
        echo         '<th>Permalink</th>';
        echo         '<th>Created (UTC)</th>';
        echo         '<th>Updated (UTC)</th>';
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

            $created            = new DateTime($blogpost->created);
            $display_created    = $created->format('j M Y H:i:s');

            $updated            = new DateTime($blogpost->updated);
            $display_updated    = $updated->format('j M Y H:i:s');

            $published          = (!$blogpost->draft && !$blogpost->deleted) ? $yes : $no;
            $draft              = $blogpost->draft ? $yes : $no;
            $deleted            = $blogpost->deleted ? $yes : $no;

            $menuitems          = array();

            $menuitems[]        = array('href' => $blogpost->permalink.'?action=edit',
                                        'rel' => 'nofollow',
                                        'text' => 'Edit');

            if ($blogpost->deleted)
            {
                $menuitems[]        = array('href' => $blogpost->permalink.'?action=undelete',
                                            'rel' => 'nofollow',
                                            'text' => 'Undelete');

                $prompt             = 'Purge this blogpost?';

                $menuitems[]        = array('href' => 'javascript:void(0);',
                                            'onclick' => "confirm_delete('$prompt', '$blogpost->permalink?action=purge');",
                                            'rel' => 'nofollow',
                                            'text' => '<b>Purge</b>');
            }
            else
            {
                if ($blogpost->draft)
                {
                    $menuitems[]        = array('href' => $blogpost->permalink.'?action=publish',
                                                'rel' => 'nofollow',
                                                'text' => 'Publish');
                }
                else
                {
                    $menuitems[]        = array('href' => $blogpost->permalink.'?action=unpublish',
                                                'rel' => 'nofollow',
                                                'text' => 'Unpublish');
                }

                $prompt = 'Delete this blogpost?';

                $menuitems[]        = array('href' => 'javascript:void(0);',
                                            'onclick' => "confirm_delete('$prompt', '$blogpost->permalink?action=delete');",
                                            'rel' => 'nofollow',
                                            'text' => 'Delete');
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
            echo    "<td sorttable_customkey='$blogpost->timestamp'>$display_timestamp</td>";
            echo    "<td>$published</td>";
            echo    "<td>$draft</td>";
            echo    "<td>$deleted</td>";
            echo    "<td>$blogpost->uid</td>";
            echo    "<td><a href='$blogpost->permalink' target='_blank'>$blogpost->permalink</a></td>";
            echo    "<td sorttable_customkey='$blogpost->created'>$display_created</td>";
            echo    "<td sorttable_customkey='$blogpost->updated'>$display_updated</td>";
            echo    '<td align="center" class="nonprinting">['.$menu_html.']</td>';
            echo '</tr>';
        }

        echo   '</tbody>';

        echo   '<tfoot>';
        echo     '<tr align="center">';
        echo      '<th><i>Total: '.$blogpost_count['total'].'</i></th>';
        echo      '<th></th>';
        echo      '<th></th>';
        echo      '<th><i>'.$blogpost_count['published'].'</i></th>';
        echo      '<th><i>'.$blogpost_count['draft'].'</i></th>';
        echo      '<th><i>'.$blogpost_count['deleted'].'</i></th>';
        echo      '<th></th>';
        echo      '<th></th>';
        echo      '<th></th>';
        echo      '<th></th>';
        echo      '<th></th>';
        echo     '</tr>';
        echo   '</tfoot>';

        echo '</table>';
    }


    function get_blogpost_counts($blogposts)
    {
        $blogpost_count = array();

        $blogpost_count['total']        = count($blogposts);

        $blogpost_count['draft']        = 0;
        $blogpost_count['published']    = 0;
        $blogpost_count['deleted']      = 0;

        foreach ($blogposts as $blogpost)
        {
            if (!$blogpost->draft && !$blogpost->deleted)
            {
                ++$blogpost_count['published'];
            }

            if ($blogpost->deleted)
            {
                ++$blogpost_count['deleted'];
            }

            if ($blogpost->draft)
            {
                ++$blogpost_count['draft'];
            }
        }
        return $blogpost_count;
    }


    function administer_blog()
    {
        $cmd_action = isset($_GET['cmd_action']) ? $_GET['cmd_action'] : '';

        if ('import' == $cmd_action)
        {
            import_blogposts();
        }
        else
        {
            $db                             = new db_credentials();
            $blog_table                     = new BlogTable($db);

            $query_params                   = new BlogTableQueryParams();

            $query_params->include_drafts   = true;
            $query_params->include_deleted  = true;

            $blogposts                      = $blog_table->get_all($query_params);

            $blogpost_count                 = get_blogpost_counts($blogposts);

            echo '<br><h2>Administer Blog</h2><br>';

            show_blogposts_table($blogposts);

            echo '<p>&nbsp;</p>';
            echo '<p>';
            echo   '<a href="/pages/admin?target=blog&cmd_action=import">Import Blogposts</a>';
            echo   ' | ';
            echo   '<a href="/pages/admin?target=blog&cmd_action=export">Export Blogposts</a>';
            echo '</p>';

            echo '<p>&nbsp;</p>';
        }
    }

?>
