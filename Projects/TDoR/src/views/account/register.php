<?php
    /**
     * Register account page
     *
     */


    require_once('models/users.php');


    $form_action_url = htmlspecialchars($_SERVER["PHP_SELF"]);


    // Define variables and initialize with empty values
    $username = $email = $password = $confirm_password = "";
    $username_err = $email_err = $password_err = $confirm_password_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $db             = new db_credentials();

        $users_table    = new Users($db);

        // Validate username
        $username       = trim($_POST["username"]);
        $email          = trim($_POST["email"]);

        if (is_bot(get_user_agent() ) )
        {
            $email_err = 'Sorry, it looks like you might be a bot. If we are wrong about this please let us know';
        }
        else if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) )
        {
            $email_err = "Please enter a valid email address.";
        }
        else
        {
            $user = $users_table->get_user_from_email_address($email);

            if (!empty($user->username) )
            {
                $email_err = "Sorry! This email address is already taken.";
            }
        }

        if (empty($username) )
        {
            $username_err = "Please enter a username.";
        }
        else
        {
            $user = $users_table->get_user($username);

            if (!empty($user->username) )
            {
                $username_err = "Sorry! This username is already taken.";
            }
        }

        // Validate password
        $password = trim($_POST['password']);

        if (empty($password) )
        {
            $password_err = "Please enter a password.";
        }
        elseif (strlen($password) < 10)
        {
            $password_err = "Password must be at least 10 characters long.";
        }

        // Validate confirm password
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($confirm_password) )
        {
            $confirm_password_err = 'Please confirm password.';
        }
        else
        {
            if ($password != $confirm_password)
            {
                $confirm_password_err = 'Passwords did not match.';
            }
        }

        // Check input errors before inserting in database
        if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) )
        {
            // Is this the first user? If so, we need to make them an admin and activate automatically
            $user_count = count($users_table->get_all() );

            $user = new User;

            $user->username         = $username;
            $user->email            = $email;
            $user->hashed_password  = password_hash($password, PASSWORD_DEFAULT);   // Creates a password hash

            $user->roles            = 'I';                                          // Default role = API user
            $user->api_key          = $users_table->generate_api_key(user);
            $user->activated        = 0;                                            // The new user will have to be activated before they can login.
            $user->created_at       = date("Y-m-d H:i:s", time() );

            if ($user_count === 0)
            {
                // This is the first user, so activate automatically and make them an admin    
                $user->roles       .= 'EA';
                $user->activated    = 1;
            }

            if ($users_table->add_user($user) )
            {
                // Notify the admin that a user has registered
                $host       = raw_get_host();
                $subject    = "New user registered on $host";
                $html       = "<p>The user <b>$user->username</b> ($user->email) has just registered on $host.</p><p>&nbsp;</p><p><a href='$host/pages/admin?target=users'><b>Administer Users</b></a></p>";

                send_email(ADMIN_EMAIL_ADDRESS, NOTIFY_EMAIL_ADDRESS, $subject, $html);

                // Redirect to login page
                if (redirect_to('/account/login') )
                {
                    exit;
                }
            }
            else
            {
                echo "Something went wrong. Please try again later.";
            }
        }
    }



    ////////////////////////////////////////////////////////////////////////////////
    // Form content

    echo '<h2>Sign Up</h2>';
    echo '<p>Please fill in the form below to create an account:</p><br>';


    echo "<form action='$form_action_url' method='post'>";
    
    
    // Email
    echo   '<div class="clearfix">';
    echo     '<div class="grid_2">';
    echo       '<label>Email:</label>';
    echo     '</div>';

    echo     '<div class="grid_10">';
    echo       "<input type='text' name='email' value='$email' />";

    if (!empty($email_err) )
    {
        echo   "<p class='account-error'>$email_err</p>";
    }
    echo     '</div>';
    echo   '</div>';


    // Username
    echo   '<div class="clearfix">';
    echo     '<div class="grid_2">';
    echo       '<label>Username:</label>';
    echo     '</div>';

    echo     '<div class="grid_10">';
    echo       "<input type='text' name='username' value='$username' />";

    if (!empty($username_err) )
    {
        echo   "<p class='account-error'>$username_err</p>";
    }
    echo     '</div>';
    echo   '</div>';


    // Password
    echo   '<div class="clearfix">';
    echo     '<div class="grid_2">';
    echo       '<label>Password:</label>';
    echo     '</div>';

    echo     '<div class="grid_10">';
    echo       "<input type='password' name='password' />";

    if (!empty($password_err) )
    {
        echo   "<p class='account-error'>$password_err</p>";
    }
    echo     '</div>';
    echo   '</div>';


    // Password confirmation
    echo   '<div class="clearfix">';
    echo     '<div class="grid_2">';
    echo       '<label>Confirm Password:</label>';
    echo     '</div>';

    echo     '<div class="grid_10">';
    echo       "<input type='password' name='confirm_password' value='$confirm_password' />";

    if (!empty($confirm_password_err) )
    {
        echo   "<p class='account-error'>$confirm_password_err</p>";
    }
    echo     '</div>';
    echo   '</div>';

 
    // Submit & Reset buttons
    echo   '<div class="clearfix">';
    echo     '<div class="grid_2"></div>';
    echo     '<div class="grid_10">';
    echo       '<input type="submit" class="button-blue" value="Submit" />&nbsp;';
    echo       '<input type="reset" class="button-gray" value="Reset" />';
    echo       '<br>&nbsp;&nbsp;If you already have an account, you can <a href="/account/login"><b>login here</b></a>.';
    echo     '</div>';
    echo   '</div>';

    echo '</form>';

?>
