<?php
    require_once('connection.php');

    // This index.php file receives all requests - override "controller" and "action" to choose a specific page.
    if (isset($_GET['controller']) && isset($_GET['action']) )
    {
        $controller = $_GET['controller'];
        $action     = $_GET['action'];
    }
    else
    {
        // By default, display the static homepage.
        $controller = 'pages';
        $action     = 'home';
    }

    // Page layout
    require_once('views/layout.php');

?>