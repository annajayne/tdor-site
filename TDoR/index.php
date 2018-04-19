<?php
    // MVC (though arguably more MVP) sample based on the article at http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/
    //
    //
    // Turn up the MySQL error reporting a bit (but don't report missing indices, as that errors basically everywhere)
    require_once('db_credentials.php');
    require_once('create_db.php');
    require_once('connection.php');
    require_once('misc.php');


    log_text("Hello World!");

    // Credentials and DB name are coded in db_credentials.php
    $db = new db_credentials();

    // If the database doesn't exist, create it and add some dummy data
    if (!db_exists($db) )
    {
        create_db($db);
    }

    if (!table_exists($db, 'posts') )
    {
        add_tables($db);

        add_dummy_data($db, "Douglas Adams", "Space is big. You just won't believe how vastly, hugely, mind-bogglingly big it is. I mean, you may think it's a long way down the road to the chemist's, but that's just peanuts to space.");
        add_dummy_data($db, "Olivia Wilde", "When is my phone going to learn that I never, ever meant to write 'ducked up'?");
    }

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