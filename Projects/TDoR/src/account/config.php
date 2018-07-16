<?php

    require_once('./../db_credentials.php');        // Credentials and DB name are coded in db_credentials.php
    require_once('password.php');                   // Required for PHP < 5.5 (ref https://github.com/ircmaxell/password_compat)

    $db = new db_credentials();

    // Attempt to connect to MySQL database
    try
    {
        $pdo = new PDO("mysql:host=" . $db->servername . ";dbname=" . $db->dbname, $db->username, $db->password);

        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch (PDOException $e)
    {
        die("ERROR: Could not connect. " . $e->getMessage() );
    }
?>