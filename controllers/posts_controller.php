<?php
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
            // Store all the posts in a variable
            $posts = Post::all();

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