<?php
    /**
     * Edit the current blogpost.
     *
     */


    /**
     * Determine if the given blogpost has been changed as a result of editing.
     *
     * @param Report $post                  The original post.
     * @param Report $updated_post          The edited post.
     * @return boolean                      true if changed; false otherwise.
     */
    function is_post_edited($post, $updated_post)
    {
        if ( ($updated_post->uid !== $post->uid) ||
             ($updated_post->draft !== $post->draft) ||
             ($updated_post->title !== $post->title) ||
             ($updated_post->timestamp !== $post->timestamp) ||
             ($updated_post->content !== $post->content) )
        {
            return true;
        }
        return false;
    }


    if (is_admin_user() )
    {
        if (isset($_POST['submit']) )
        {
            $datetime                       = new DateTime($_POST['time']);
            $time                           = $datetime->format('H:i:s');

            $updated_post                   = new Post;
            $updated_post->set_from_post($post);

            $updated_post->title            = $_POST['title'];
            $updated_post->timestamp        = date_str_to_iso($_POST['date']).' '.$time;
            $updated_post->draft            = ('published' != $_POST['published']) ? true : false;
            $updated_post->content          = $_POST['text'];

            $updated_post->permalink      = Posts::create_permalink($updated_post);

            if (is_post_edited($post, $updated_post) )
            {
                $db             = new db_credentials();
                $posts_table    = new Posts($db);
                
                if ($posts_table->update_post($updated_post) )
                {
                    redirect_to($post->permalink);
                }
            }
        }

        echo '<h2>Edit Post</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';

        $datetime = new DateTime($post->timestamp);

        $timestamp = $datetime->format('g:ia');

        // Date
        echo     '<div class="grid_4">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.date_str_to_display_date($post->timestamp).'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Time
        echo     '<div class="grid_4">';
        echo       '<label for="time">Time:<br></label>';
        echo       '<input type="text" name="time" id="timepicker" class="form-control" placeholder="Time" value="'.$timestamp.'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Draft
        echo     '<div class="grid_4">';
        echo       '<br>';
        echo       '<input type="radio" id="draft" name="published" value="draft" '.($post->draft ? 'checked' : '').' />&nbsp;&nbsp;';
        echo       '<label for="draft">Draft</label>&nbsp;&nbsp;';
        echo       '<input type="radio" id="published" name="published" value="published" '.(!$post->draft ? 'checked' : '').' />&nbsp;&nbsp;';
        echo       '<label for="published">Published</label><br>';
        echo     '</div>';


        // Title
        echo     '<div class="grid_12">';
        echo       '<label for="name">Title:<br></label>';
        echo       '<input type="text" name="title" id="title" value="'.htmlspecialchars($post->title).'" style="width:100%;" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Text
        echo     '<div class="grid_12">';
        echo       '<label for="text">text:<br></label>';
        echo       '<textarea name="text" id="text" style="width:100%; height:500px;" onkeyup="javascript:set_text_colours()">'.$post->content.'</textarea>';
        echo     '</div>';


        // OK/Cancel
        echo     '<br>';
        echo     '<div class="grid_12" align="right">';
        echo       '<input type="submit" name="submit" value="Submit" />&nbsp;&nbsp;';
        echo       '<input type="button" name="cancel" id="cancel" value="Cancel" class="btn btn-success" onclick="javascript:history.back()" />';
        echo     '</div>';


        echo   '</div>';
        echo '</form>';


        echo '<script src="/js/blog_editing.js"></script>';
    }


?>