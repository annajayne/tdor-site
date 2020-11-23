<?php
    /**
     * Edit the current blogpost.
     *
     */


    /**
     * Determine if the given blogpost has been changed as a result of editing.
     *
     * @param Report $blogpost              The original blogpost.
     * @param Report $updated_blogpost      The edited blogpost.
     * @return boolean                      true if changed; false otherwise.
     */
    function is_post_edited($blogpost, $updated_blogpost)
    {
        if ( ($updated_blogpost->uid !== $blogpost->uid) ||
             ($updated_blogpost->draft !== $blogpost->draft) ||
             ($updated_blogpost->title !== $blogpost->title) ||
             ($updated_blogpost->timestamp !== $blogpost->timestamp) ||
             ($updated_blogpost->content !== $blogpost->content) )
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

            $updated_blogpost               = new BlogPost;
            $updated_blogpost->set_from_post($blogpost);

            $updated_blogpost->title        = $_POST['title'];
            $updated_blogpost->timestamp    = date_str_to_iso($_POST['date']).' '.$time;
            $updated_blogpost->draft        = ('published' != $_POST['published']) ? true : false;
            $updated_blogpost->content      = $_POST['text'];

            $updated_blogpost->permalink    = BlogPosts::create_permalink($updated_blogpost);

            if (is_post_edited($blogpost, $updated_blogpost) )
            {
                $db             = new db_credentials();
                $posts_table    = new BlogPosts($db);
                
                if ($posts_table->update_post($updated_blogpost) )
                {
                    redirect_to($blogpost->permalink);
                }
            }
        }

        echo '<h2>Edit Blogpost</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';

        $datetime = new DateTime($blogpost->timestamp);

        $timestamp = $datetime->format('g:ia');

        // Date
        echo     '<div class="grid_4">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.date_str_to_display_date($blogpost->timestamp).'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Time
        echo     '<div class="grid_4">';
        echo       '<label for="time">Time:<br></label>';
        echo       '<input type="text" name="time" id="timepicker" class="form-control" placeholder="Time" value="'.$timestamp.'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Draft
        echo     '<div class="grid_4">';
        echo       '<br>';
        echo       '<input type="radio" id="draft" name="published" value="draft" '.($blogpost->draft ? 'checked' : '').' />&nbsp;&nbsp;';
        echo       '<label for="draft">Draft</label>&nbsp;&nbsp;';
        echo       '<input type="radio" id="published" name="published" value="published" '.(!$blogpost->draft ? 'checked' : '').' />&nbsp;&nbsp;';
        echo       '<label for="published">Published</label><br>';
        echo     '</div>';


        // Title
        echo     '<div class="grid_12">';
        echo       '<label for="name">Title:<br></label>';
        echo       '<input type="text" name="title" id="title" value="'.htmlspecialchars($blogpost->title).'" style="width:100%;" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Text
        echo     '<div class="grid_12">';
        echo       '<label for="text">text:<br></label>';
        echo       '<textarea name="text" id="text" style="width:100%; height:500px;" onkeyup="javascript:set_text_colours()">'.$blogpost->content.'</textarea>';
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