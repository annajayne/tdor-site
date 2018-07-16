<?php

    function is_logged_in()
    {
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) )
        {
            return true;
        }
        return false;
    }


    function get_logged_in_username()
    {
        if (is_logged_in() )
        {
            return $_SESSION['username'];
        }
        return '';
    }

?>