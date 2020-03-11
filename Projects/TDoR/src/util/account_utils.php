<?php

    /**
     * Determine whether a user is logged in
     *
     * @return boolean                  true if a user is logged in; false otherwise.
     */
    function is_logged_in()
    {
        if (isset($_SESSION['username']) && !empty($_SESSION['username']) )
        {
            return true;
        }
        return false;
    }


    /**
     * Determine The username of the current user
     *
     * @return string                   The name of the current user, or an empty string if no user is logged in.
     */
    function get_logged_in_username()
    {
        if (is_logged_in() )
        {
            return $_SESSION['username'];
        }
        return '';
    }


    /**
     * Return the roles of the current user
     *
     * @return string                   The roles of the current user, or an empty string if no user is logged in.
     */
    function get_user_roles()
    {
        if (is_logged_in() && isset($_SESSION['roles']) )
        {
            return $_SESSION['roles'];
        }
        return '';
    }


    /**
     * Determine whether the current user is an API user
     *
     * @return boolean                  true if the current user is an API user; false otherwise.
     */
    function is_api_user()
    {
        $roles = get_user_roles();

        if (!empty($roles) && (strpos($roles, 'I') !== false) )
        {
            return true;
        }
        return false;
    }


    /**
     * Determine whether the current user is an editor
     *
     * @return boolean                  true if the current user is an editor; false otherwise.
     */
    function is_editor_user()
    {
        $roles = get_user_roles();

        if (!empty($roles) && (strpos($roles, 'E') !== false) )
        {
            return true;
        }
        return false;
    }


    /**
     * Determine whether the current user is an admin
     *
     * @return boolean                  true if the current user is an admin; false otherwise.
     */
    function is_admin_user()
    {
        $roles = get_user_roles();

        if (!empty($roles) && (strpos($roles, 'A') !== false) )
        {
            return true;
        }
        return false;
    }


    /**
     * Determine whether the given password is valid
     *
     * @param  string   $password       The password to validate.
     * @return boolean                  true if the password is valid; false otherwise.
     */
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



    /**
     * Return a message describing the properties required of passwords for the site
     *
     * @return string           A string suitable for display in the UI.
     */
    function get_password_validity_msg()
    {
        return 'Passwords must be at least 10 characters long and include at least one upper case letter and one number.';
    }



    /**
     * Support class for the account pages (registration, password reset etc.)
     *
     */
    class account_params
    {
        /** @var string                     The username. */
        public  $username;

        /** @var string                     Details of any error in the username. */
        public  $username_err;

        /** @var string                     The email address. */
        public  $email;

        /** @var string                     Details of any error in the email address. */
        public  $email_err;

        /** @var string                     The password. Must meet complexity requirements defined by is_password_valid() [account_utils.php]. */
        public  $password;

        /** @var string                     Details of any error in the password. */
        public  $password_err;

        /** @var string                     The new password. Must meet complexity requirements defined by is_password_valid() [account_utils.php]. */
        public  $new_password;

        /** @var string                     Details of any error in the new password. */
        public  $new_password_err;

        /** @var string                     The password confirmation. Must match $password. */
        public  $confirm_password;

        /** @var string                     Details of any error in the password confirmation. */
        public  $confirm_password_err;

        /** @var string                     Details of any error which occured while changing a password. */
        public  $password_change_err;

        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $this->username             = '';
            $this->email                = '';
            $this->password             = '';
            $this->new_password         = '';
            $this->confirm_password     = '';

            $this->username_err         = '';
            $this->email_err            = '';
            $this->password_err         = '';
            $this->new_password_err     = '';
            $this->confirm_password_err = '';
            $this->password_change_err  = '';
        }

    }

?>