<?php
    /**
     * Logout account page
     *
     */


    // Unset all of the session variables
    $_SESSION = array();

    // Destroy the session.
    session_destroy();

    // Redirect to the login page
    if (redirect_to('/account/login') )
    {
        exit;
    }
?>