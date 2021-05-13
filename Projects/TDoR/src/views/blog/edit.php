<?php
    /**
     * Edit the current blogpost.
     *
     */
    require_once('util/datetime_utils.php');                // For date_str_to_iso()
    require_once('util/blog_utils.php');


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
             ($updated_blogpost->subtitle !== $blogpost->subtitle) ||
             ($updated_blogpost->thumbnail_filename !== $blogpost->thumbnail_filename) ||
             ($updated_blogpost->thumbnail_caption !== $blogpost->thumbnail_caption) ||
             ($updated_blogpost->timestamp !== $blogpost->timestamp) ||
             ($updated_blogpost->content !== $blogpost->content) )
        {
            return true;
        }
        return false;
    }


    if (is_admin_user() )
    {
        $timezone                                   = new DateTimeZone('UTC');

        if (isset($_POST['submit']) )
        {
            $current_timestamp                      = gmdate("Y-m-d H:i:s");

            $datetime                               = new DateTime($_POST['time'], $timezone);
            $time                                   = $datetime->format('H:i:s');

            $updated_blogpost                        = new Blogpost;
            $updated_blogpost->set_from_post($blogpost);

            $updated_blogpost->title                = $_POST['title'];
            $updated_blogpost->subtitle             = $_POST['subtitle'];
            $updated_blogpost->thumbnail_filename   = $_POST['thumbnail_filename'];
            $updated_blogpost->thumbnail_caption    = $_POST['thumbnail_caption'];
            $updated_blogpost->timestamp            = date_str_to_iso($_POST['date']).' '.$time;
            $updated_blogpost->draft                = ('published' != $_POST['published']) ? true : false;
            $updated_blogpost->content              = strip_host_from_image_links($_POST['text']);
            $updated_blogpost->permalink            = BlogTable::create_permalink($updated_blogpost);

            if (is_post_edited($blogpost, $updated_blogpost) )
            {
                $updated_blogpost->updated          = $current_timestamp;

                $db                                 = new db_credentials();
                $blog_table                         = new BlogTable($db);

                if ($blog_table->update($updated_blogpost) )
                {
                    BlogEvents::blogpost_updated($updated_blogpost);

                    redirect_to($blogpost->permalink);
                }
            }
        }

        echo '<h2>Edit Blogpost</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';

        $datetime = new DateTime($blogpost->timestamp);

        $timestamp = $datetime->format('g:ia');

        $blogpost->content = add_host_to_image_links($blogpost->content);

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


        // Draft/Published
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


        // Subtitle
        echo     '<div class="grid_12">';
        echo       '<label for="name">Subtitle:<br></label>';
        echo       '<input type="text" name="subtitle" id="subtitle" maxlength="'.BLOG_SUBTITLE_MAX_CHARS.'" value="'.htmlspecialchars($blogpost->subtitle).'" style="width:100%;" onkeyup="javascript:set_text_colours()" />';
        echo       '<span class="blog-editor-input-hint" id="subtitle-hint">Use to override the summary text shown in blogposts index pages. May be left blank.</span>';
        echo     '</div>';


        // Thumbnail image
        echo     '<div class="grid_12">';
        echo       '<label for="name">Thumbnail image:<br></label>';
        echo       '<input type="text" name="thumbnail_filename" id="thumbnail_filename" aria-describedby="thumbnail-path-hint" value="'.htmlspecialchars($blogpost->thumbnail_filename).'" style="width:100%;" onkeyup="javascript:set_text_colours()" />';
        echo       '<span class="blog-editor-input-hint" id="thumbnail-path-hint">Image paths can be external, specified with respect to the site root using a leading /, or relative to the blog/content folder.</span>';
        echo     '</div>';


        // Thumbnail caption
        echo     '<div class="grid_12">';
        echo       '<label for="name">Thumbnail caption:<br></label>';
        echo       '<input type="text" name="thumbnail_caption" id="thumbnail_caption" value="'.htmlspecialchars($blogpost->thumbnail_caption).'" style="width:100%;" onkeyup="javascript:set_text_colours()" />';
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