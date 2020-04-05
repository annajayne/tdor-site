<?php
    /**
     * Add a new blogpost.
     *
     */


    if (is_admin_user() )
    {
        $db                     = new db_credentials();
        $posts_table            = new Posts($db);
        
        $post                   = new Post;

        if (isset($_POST['submit']) )
        {
            $datetime           = new DateTime($_POST['time']);
            $time               = $datetime->format('H:i:s');

            $post->author       = get_logged_in_username();
            $post->uid          = $posts_table->create_uid();
            
            $post->title        = $_POST['title'];
            $post->timestamp    = date_str_to_iso($_POST['date']).' '.$time;
            $post->content      = $_POST['text'];

            $post->permalink    = Posts::create_permalink($post);

            if ($posts_table->add_post($post) )
            {
                redirect_to($post->permalink);
            }
        }


        $datetime               = new DateTime();

        $date_created           = date_str_to_display_date($datetime->format('d M Y') );
        $time_created           = $datetime->format('g:ia');


        echo '<h2>Add Post</h2><br>';

        echo '<form action="" method="POST" enctype="multipart/form-data">';
        echo   '<div>';


        // Date
        echo     '<div class="grid_6">';
        echo       '<label for="date">Date:<br></label>';
        echo       '<input type="text" name="date" id="datepicker" class="form-control" placeholder="Date" value="'.$date_created.'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Time
        echo     '<div class="grid_6">';
        echo       '<label for="time">Time:<br></label>';
        echo       '<input type="text" name="time" id="timepicker" class="form-control" placeholder="Time" value="'.$time_created.'" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Title
        echo     '<div class="grid_12">';
        echo       '<label for="name">Title:<br></label>';
        echo       '<input type="text" name="title" id="title" value="'.htmlspecialchars($post->title).'" style="width:100%;" onkeyup="javascript:set_text_colours()" />';
        echo     '</div>';


        // Text
        echo     '<div class="grid_12">';
        echo       '<label for="text">Text:<br></label>';
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