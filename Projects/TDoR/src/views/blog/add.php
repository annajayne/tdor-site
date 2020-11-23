<?php
    /**
     * Add a new blogpost.
     *
     */


    if (is_admin_user() )
    {
        $db                         = new db_credentials();
        $blogposts_table            = new BlogPosts($db);
        
        $blogpost                   = new BlogPost;

        if (isset($_POST['submit']) )
        {
            $datetime               = new DateTime($_POST['time']);
            $time                   = $datetime->format('H:i:s');

            $blogpost->author       = get_logged_in_username();
            $blogpost->uid          = $blogposts_table->create_uid();
            $blogpost->draft        = ('published' != $_POST['published']) ? true : false;
            $blogpost->title        = $_POST['title'];
            $blogpost->timestamp    = date_str_to_iso($_POST['date']).' '.$time;
            $blogpost->content      = $_POST['text'];

            $blogpost->permalink    = BlogPosts::create_permalink($blogpost);

            if ($blogposts_table->add_post($blogpost) )
            {
                redirect_to($blogpost->permalink);
            }
        }


        $datetime                   = new DateTime();

        $date_created               = date_str_to_display_date($datetime->format('d M Y') );
        $time_created               = $datetime->format('g:ia');


        echo '<h2>Add Blogpost</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';


        // Date
        echo     '<div class="grid_4">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.$date_created.'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Time
        echo     '<div class="grid_4">';
        echo       '<label for="time">Time:<br></label>';
        echo       '<input type="text" name="time" id="timepicker" class="form-control" placeholder="Time" value="'.$time_created.'" onkeyup="javascript:set_text_colours()" />';
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