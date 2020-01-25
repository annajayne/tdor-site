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


    function get_user_roles()
    {
        if (is_logged_in() && isset($_SESSION['roles']) )
        {
            return $_SESSION['roles'];
        }
        return '';
    }


    function is_api_user()
    {
        $roles = get_user_roles();

        if (!empty($roles) && (strpos($roles, 'I') !== false) )
        {
            return true;
        }
        return false;
    }


    function is_editor_user()
    {
        $roles = get_user_roles();

        if (!empty($roles) && (strpos($roles, 'E') !== false) )
        {
            return true;
        }
        return false;
    }


    function is_admin_user()
    {
        $roles = get_user_roles();

        if (!empty($roles) && (strpos($roles, 'A') !== false) )
        {
            return true;
        }
        return false;
    }


    function is_password_valid($password)
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);

        if (!$uppercase || !$lowercase || !$number || (strlen($password) < 10) )
        {
            return false;
        }
        return true;
    }


    function get_password_validity_msg()
    {
        return 'Passwords must be at least 10 characters long and include at least one upper case letter and one number';
    }

?>