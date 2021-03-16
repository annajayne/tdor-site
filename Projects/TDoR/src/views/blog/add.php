<?php
    /**
     * Add a new blogpost.
     *
     */


    if (is_admin_user() )
    {
        $db                                 = new db_credentials();
        $blog_table                    = new BlogTable($db);

        $blogpost                           = new Blogpost;

        if (isset($_POST['submit']) )
        {
            $current_timestamp              = gmdate("Y-m-d H:i:s");

            $datetime                       = new DateTime($_POST['time']);
            $time                           = $datetime->format('H:i:s');

            $blogpost->author               = get_logged_in_username();
            $blogpost->uid                  = $blog_table->create_uid();
            $blogpost->draft                = ('published' != $_POST['published']) ? true : false;
            $blogpost->title                = $_POST['title'];
            $blogpost->thumbnail_filename   = $_POST['thumbnail_filename'];
            $blogpost->thumbnail_caption    = $_POST['thumbnail_caption'];
            $blogpost->timestamp            = date_str_to_iso($_POST['date']).' '.$time;
            $blogpost->content              = $_POST['text'];
            $blogpost->permalink            = BlogTable::create_permalink($blogpost);
            $blogpost->created              = $current_timestamp;
            $blogpost->updated              = $current_timestamp;

            if ($blog_table->add($blogpost) )
            {
                BlogEvents::blogpost_added($blogpost);

                redirect_to($blogpost->permalink);
            }
        }


        $datetime                   = new DateTime();

        $blogpost_date               = date_str_to_display_date($datetime->format('d M Y') );
        $blogpost_time               = $datetime->format('g:ia');


        echo '<h2>Add Blogpost</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';


        // Date
        echo     '<div class="grid_4">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.$blogpost_date.'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Time
        echo     '<div class="grid_4">';
        echo       '<label for="time">Time:<br></label>';
        echo       '<input type="text" name="time" id="timepicker" class="form-control" placeholder="Time" value="'.$blogpost_time.'" onkeyup="javascript:set_text_colours()" />';
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


        // Thumbnail image
        echo     '<div class="grid_12">';
        echo       '<label for="name">Thumbnail image:<br></label>';
        echo       '<input type="text" name="thumbnail_filename" id="thumbnail_filename" aria-describedby="thumbnail-path-hint" value="'.htmlspecialchars($blogpost->thumbnail_filename).'" style="width:100%;" onkeyup="javascript:set_text_colours()" /><br>';
        echo       '<span class="blog-editor-input-hint" id="thumbnail-path-hint">Image paths can be external, specified with respect to the site root using a leading /, or relative to the blog/content folder.</span>';
        echo     '</div>';

        // Thumbnail caption
        echo     '<div class="grid_12">';
        echo       '<label for="name">Thumbnail caption:<br></label>';
        echo       '<input type="text" name="thumbnail_caption" id="thumbnail_caption" value="'.htmlspecialchars($blogpost->thumbnail_caption).'" style="width:100%;" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Text
        echo     '<div class="grid_12">';
        echo       '<label for="text">Text:<br></label>';
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