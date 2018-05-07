<?php
    require_once('views/posts/posts_table_view_impl.php');
    require_once('views/posts/posts_details_view_impl.php');


    // Controller for posts (database pages)
    //
    // Supported actions:
    //
    //      index
    //      show
    //      create (TODO)
    //      update (TODO)
    //
    class PostsController
    {
        public function index()
        {
            $posts_available    = Post::has_posts();
            $post_date_range    = Post::get_minmax_dates();

            $tdor_to_year       = get_tdor_year(new DateTime($post_date_range[1]) );

            $date_from          = '1 Oct '.($tdor_to_year - 1);
            $date_to            = '30 Sep '.$tdor_to_year;

            if (isset($_GET['filter']) )
            {
                $filter = $_GET['filter'];
            }

            if (isset($_GET['from']) && isset($_GET['to']) )
            {
                $date_from = $_GET['from'];
                $date_to = $_GET['to'];
            }
             
           
            if (!empty($date_from) && !empty($date_to) )
            {
                $posts = Post::all_in_range($date_from, $date_to, $filter);
            }
            else
            {
                // Store all the posts in a variable
                $posts = Post::all($filter);
            }

            require_once('views/posts/index.php');
        }


        public function show()
        {
            // We expect a url of the form ?controller=posts&action=show&id=x
            // (without an id we just redirect to the error page as we need the post id to find it in the database)
            if (!isset($_GET['id']) )
            {
                return call('pages', 'error');
            }

            // Use the given id to locate the corresponding post
            $post = Post::find($_GET['id']);

            require_once('views/posts/show.php');
        }
    }


?>