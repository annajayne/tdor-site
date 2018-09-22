<?php
    /**
     * Main entrypoint.
     * 
     * Based on the MVC (though arguably more MVP) sample described in the article at http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/.
     *
     */

    require_once('defines.php');
    require_once('db_credentials.php');
    require_once('db_utils.php');
    require_once('connection.php');
    require_once('csv_import.php');
    require_once('utils.php');
    require_once('misc.php');
    require_once('display_utils.php');
    require_once('account/account_utils.php');
    require_once("lib/phpqrcode/qrlib.php");


    // Initialise the session
    session_start();

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

        if ( ($element_count == 2) && ($elements[0] === 'pages') )
        {
            $controller = $elements[0];

            if (str_begins_with($elements[1], 'rebuild?') )
            {
                $action     = 'rebuild';
            }
            else
            {
                switch ($elements[1])
                {
                    case 'about':   $action     = 'about';              break;
                    case 'search':  $action     = 'search';             break;
                    case 'rebuild': $action     = 'rebuild';            break;
                    default:        header('HTTP/1.1 404 Not Found');   break;
                }
            }
        }

        if ( ($element_count > 0) && ( ($elements[0] === 'reports') || str_begins_with($elements[0], 'reports?') ) )
        {
            $controller     = 'reports';

            if ($element_count === 5)
            {
                $action     = 'show';
            }
            else if  ($element_count >= 1)
            {
                // '/report', '/report/' or '/report?', '/report/year/month/' etc.
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

    if (isset($_GET['category']) )
    {
        $controller = $_GET['category'];
    }
    else if (isset($_GET['controller']) )
    {
        $controller = $_GET['controller'];
    }

    if (isset($_GET['action']) )
    {
        $action     = $_GET['action'];
    }

    if (db_exists($db) && !is_logged_in() && ($action === 'rebuild') )
    {
        // If the database exists, only allow the rebuild action if logged in.
        header('location: /account/welcome.php');
        exit;
    }

    if ( (DEV_INSTALL && !db_exists($db) ) || !table_exists($db, 'users') )
    {
        // Special case - if the database doesn't exist, attempt to create it.
        // This should only really be run on dev installs, as in production
        // creating a database requires privileges the site shouldn't have.
        $action     = 'rebuild';
    }

    switch ($action)
    {
        case 'export':
            // When exporting data we bypass layout.php as we need to send headers to initiate the download.
            require_once('views/reports/export.php');
            break;

        case 'slideshow':
            require_once('views/reports/slideshow.php');
            break;

        case 'memorial_card':
            require_once('views/reports/memorial_card.php');
            break;

        default:
            // Page layout
            require_once('views/layout.php');
            break;
    }
?>