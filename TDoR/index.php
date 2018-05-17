<?php
    // MVC (though arguably more MVP) sample based on the article at http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/
    //
    //
    // Turn up the MySQL error reporting a bit (but don't report missing indices, as that errors basically everywhere)
    require_once('defines.php');
    require_once('db_credentials.php');
    require_once('db_utils.php');
    require_once('connection.php');
    require_once('csv_import.php');
    require_once('utils.php');
    require_once('misc.php');


    // This index.php file receives all requests - override "controller" and "action" to choose a specific page.
    // by default, display the static homepage.
    $controller = 'pages';
    $action     = 'home';

    if (ENABLE_FRIENDLY_URLS)
    {
        $path = ltrim($_SERVER['REQUEST_URI'], '/');    // Trim leading slash(es)
        $elements = explode('/', $path);                // Split path on slashes

        // e.g. tdor.annasplace.me.uk/reports/year/month/day/name
        $element_count = count($elements);

        if ($element_count == 1)
        {
            $controller = 'pages';
            switch ($elements[0])
            {
                case 'about':   $action     = 'about';              break;
                case 'search':  $action     = 'search';             break;
                case 'rebuild': $action     = 'rebuild';            break;
                case 'reports':                                     break;
                default:        header('HTTP/1.1 404 Not Found');   break;
            }
        }

        if ( ($element_count > 0) && ( ($elements[0] == 'reports') || str_begins_with($elements[0], 'reports?') ) )
        {
            $controller     = 'reports';

            if ($element_count === 5)
            {
                $action     = 'show';
            }
            else if ( ($element_count === 1) || (
                    ($element_count === 2) && str_begins_with($elements[1], '?') ) )
            {
                $action     = 'index';
            }
            else
            {
                header('HTTP/1.1 404 Not Found');
            }
        }
    }

    log_text("Hello World!");

    // Credentials and DB name are coded in db_credentials.php
    $db = new db_credentials();


    if (isset($_GET['controller']) )
    {
        $controller = $_GET['controller'];
    }

    if (isset($_GET['action']) )
    {
        $action     = $_GET['action'];
    }

    if (SHOW_REBUILD_MENUITEM)
    {
        // Special case - if the database doesn't exist, create it.
        if (!db_exists($db) )
        {
            $action     = 'rebuild';
        }
    }

    // Page layout
    require_once('views/layout.php');

?>