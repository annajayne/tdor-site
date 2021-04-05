<?php
    /**
     * Main entrypoint.
     *
     * Based on the MVC (though arguably more MVP) sample described in the article at http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/.
     *
     */

    require_once('defines.php');
    require_once('models/db_credentials.php');
    require_once('db_utils.php');
    require_once('connection.php');
    require_once('utils.php');
    require_once('util/misc.php');
    require_once('display_utils.php');
    require_once('util/account_utils.php');                 // Account utilities
    require_once('util/url_decoder.php');                   // URL decoder
    require_once('util/csv_importer.php');
    require_once("lib/phpqrcode/qrlib.php");                // QR code generation
    require_once("lib/password_compat/password.php");       // Required for PHP < 5.5 (ref https://github.com/ircmaxell/password_compat)


    // Initialise the session
    session_start();

    // This index.php file receives all requests - override "controller" and "action" to choose a specific page.
    // by default, display the static homepage.
    $controller     = 'pages';
    $action         = 'home';

    $url            = $_SERVER['REQUEST_URI'];
    $url_decoder    = new UrlDecoder();

    $redirected_url = $url_decoder->get_redirected_url($url);

    if (!empty($redirected_url) )
    {
        // Permanent redirects for legacy URLs
        $host = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];

        header("Location: $host/$redirected_url");
        exit;
    }

    if ($url_decoder->decode($url) )
    {
        $decoder_controller = $url_decoder->get_controller();
        $decoder_action     = $url_decoder->get_action();

        if (!empty($decoder_controller) && !empty($decoder_action) )
        {
            $controller = $decoder_controller;
            $action     = $decoder_action;
        }
    }

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

    if (db_exists($db) && !is_admin_user() && ($action === 'admin') )
    {
        // If the database exists, only allow admin actions if logged in.
        header('location: /account/welcome.php');
        exit;
    }

    if ( (DEV_INSTALL && !db_exists($db) ) || !table_exists($db, 'users') )
    {
        // Special case - if the database doesn't exist, attempt to create it.
        // This should only really be run on dev installs, as in production
        // creating a database requires privileges the site shouldn't have.
        $action     = 'admin';
    }


    // Depending on the action, we may need to bypass the normal site template to (for example) initiate a download.
    switch ($action)
    {
        case 'export':
            require_once('views/reports/export.php');
            break;

        case 'slideshow':
            require_once('views/reports/slideshow.php');
            break;

        case 'memorial_card':
            require_once('views/reports/memorial_card.php');
            break;

        case 'presentation':
            require_once('views/reports/presentation.php');
            break;

        case 'get_tweet_text':
            require_once('views/reports/get_tweet_text.php');
            break;

        case 'rss':
            require_once('views/reports/rss.php');
            break;

        default:
            // Use the site template for all other actions
            require_once('views/template.php');
            break;
    }
?>