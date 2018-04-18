<?php
    // Outputs the HTML corresponding to the given controller and view.


    // Identify and execute the specified action on the given controller.
    //
    // Two controllers are implemented: pages (static pages) and posts (database pages).
    // The actions implemented are controller dependent (e.g. 'show' and 'index' for posts, and 'home' and 'error' for pages).
    //
    function call($controller, $action)
    {
        // We need the file that matches the controller name (e.g. posts_controller.php)
        require_once('controllers/' . $controller . '_controller.php');

        // Create a new instance of the needed controller
        switch ($controller)
        {
            case 'pages':
                $controller = new PagesController();
                break;

            case 'posts':
                // The controller uses the model below to query the database. See PostsController::show() and PostsController::index()
                require_once('models/post.php');

                $controller = new PostsController();
                break;
        }

        // call the action
        $controller->{ $action }();
    }


    // Build a list of the controllers we have and the actions they support
    $controllers = array('pages' => array('home', 'error'),
                         'posts' => array('index', 'show') );

    // Check that the requested controller and action are both supported
    // (requesting anything else will redirect to the 'error' action of the pages controller).
    if (array_key_exists($controller, $controllers) && in_array($action, $controllers[$controller]) )
    {
        call($controller, $action);
    }
    else
    {
        call('pages', 'error');
    }

?>