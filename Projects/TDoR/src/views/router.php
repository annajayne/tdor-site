<?php
    /**
     * Outputs the HTML corresponding to the given controller and view.
     */

     require_once('controllers/controllers.php');



    /**
     * Get the specified controller.
     *
     * @param string $controller        The name of the controller.
     */
    function get_controller($controller)
    {
        // Create a new instance of the requested controller
        $controller_object = null;

        switch ($controller)
        {
            case 'account':
                $controller_object = new AccountController();
                break;

            case 'reports':
                $controller_object = new ReportsController();
                break;

            case 'blog':
                $controller_object = new BlogController();
                break;

            case 'pages':
            default:
                $controller_object = new PagesController();
                break;
        }

        return $controller_object;
    }


    /**
     * Call the specified action of the given controller.
     *
     * Note that the actions implemented are controller dependent (e.g. 'show' and 'index' for posts, and 'home' and 'error' for pages).
     *
     * @param string $controller        The name of the controller.
     * @param string $action            The name of the action.
     */
    function call($controller, $action)
    {
        $controller_object = get_controller($controller);

        // call the action
        $controller_object->{$action}();
    }


    /**
     * Get the appropriate title for the specified action on the given controller.
     *
     * @param string $controller        The name of the controller.
     * @param string $action            The name of the action.
     * @return string                   The name of the CSS content style.
     */
    function get_page_title($controller, $action)
    {
        $controller_object = get_controller($controller);

        return $controller_object->get_page_title($action);
    }


     /**
      * Get the appropriate description for the specified action on the given controller.
      *
      * @param string $controller        The name of the controller.
      * @param string $action            The name of the action.
      * @return string                   The name of the CSS content style.
      */
    function get_page_description($controller, $action)
    {
        $controller_object = get_controller($controller);

        return $controller_object->get_page_description($action);
    }


    /**
     * Get the appropriate keywords for the specified action on the given controller.
     *
     * @param string $controller        The name of the controller.
     * @param string $action            The name of the action.
     * @return string                   The name of the CSS content style.
     */
    function get_page_keywords($controller, $action)
    {
        $controller_object = get_controller($controller);

        return $controller_object->get_page_keywords($action);
    }


    /**
     * Get the appropriate thumbnail for the specified action on the given controller.
     *
     * @param string $controller        The name of the controller.
     * @param string $action            The name of the action.
     * @return string                   The page keywords.
     */
    function get_page_thumbnail($controller, $action)
    {
        $controller_object = get_controller($controller);

        return $controller_object->get_page_thumbnail($action);
    }


    /**
     * Get the appropriate content style for the specified action on the given controller.
     *
     * @param string $controller        The name of the controller.
     * @param string $action            The name of the action.
     * @return string                   The name of the CSS content style.
     */
    function get_content_style($controller, $action)
    {
        switch ($controller)
        {
            case 'reports':
                if (($action === 'index') || ($action === 'drafts') || ($action === 'recent'))
                {
                    return 'wide-content';
                }
                break;

            case 'pages':
                if (($action === 'admin') || ($action === 'search'))
                {
                    return 'wide-content';
                }
                break;

            default:
                break;
        }
        return 'content';
    }


    /**
     * Identify and execute the specified action on the given controller.
     *
     * Several controllers are implemented, e.g.: pages (static pages) and posts (database pages).
     * The actions implemented are controller dependent.
     *
     * @param string $controller        The name of the controller.
     * @param string $action            The name of the action.
     */
    function route($controller, $action)
    {
        // Build a list of the controllers we have and the actions they support
        $account_controller                 = new AccountController();
        $pages_controller                   = new PagesController();
        $blog_controller                    = new BlogController();
        $reports_controller                 = new ReportsController();

        $controllers = array($account_controller->get_name() => $account_controller->get_actions(),
                             $pages_controller->get_name() => $pages_controller->get_actions(),
                             $blog_controller->get_name() => $blog_controller->get_actions(),
                             $reports_controller->get_name() => $reports_controller->get_actions() );

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
    }

?>