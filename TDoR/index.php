<?php
    // MVC (though arguably more MVP) sample based on the article at http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/
    //
    //
    // Turn up the MySQL error reporting a bit (but don't report missing indices, as that errors basically everywhere)
    require_once('db_credentials.php');
    require_once('create_db.php');
    require_once('connection.php');
    require_once('csv_import.php');
    require_once('utils.php');
    require_once('misc.php');


    log_text("Hello World!");

    // Credentials and DB name are coded in db_credentials.php
    $db = new db_credentials();


    // This index.php file receives all requests - override "controller" and "action" to choose a specific page.
    // by default, display the static homepage.
    $controller = 'pages';
    $action     = 'home';

    if (isset($_GET['controller']) )
    {
        $controller = $_GET['controller'];
    }

    if (isset($_GET['action']) )
    {
        $action     = $_GET['action'];
    }

    // Page layout
    require_once('views/layout.php');

?>