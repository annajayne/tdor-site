<?php
    /**
     * Login account page
     *
     */
 

    require_once('models/users.php');


    $form_action_url = '/account/login';


    // Define variables and initialize with empty values
    $username = $password = "";
    $username_err = $password_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $username = trim($_POST["username"]);
        $password = trim($_POST['password']);

        if (is_bot(get_user_agent() ) )
        {
            $username_err = 'Sorry, it looks like you might be a bot. If we are wrong about that please let us know';
        }
        
        // Check if username is empty
        if (empty($username) )
        {
            $username_err = 'Please enter your username.';
        }

        // Check if password is empty
        if (empty($password) )
        {
            $password_err = 'Please enter your password.';
        }

        // Validate credentials
        if (empty($username_err) && empty($password_err) )
        {
            $db             = new db_credentials();

            $users_table    = new Users($db);
            $user           = $users_table->get_user($username);

            if (!empty($user->username) )
            {
                // The username exists
                if (password_verify($password, $user->hashed_password) )
                {
                    if ($user->activated)
                    {
                        if (empty($user->api_key) )
                        {
                            // If an API key has not yet been generated, generate and store one now
                            $user->api_key = $users_table->generate_api_key($user);

                            $users_table->update_user($user);
                        }

                        // The password is correct and the account is active, so start a new session
                        // and store copies of the relevant user properties in the session
                        session_start();

                        $_SESSION['username']   = $user->username;
                        $_SESSION['roles']      = $user->roles;
                        $_SESSION['api_key']    = $user->api_key;

                        if (redirect_to('/account') )
                        {
                            exit;
                        }
                    }
                    else
                    {
                        $password_err = 'This account has not yet been activated. Please contact <a href="mailto:tdor@translivesmatter.info">tdor@translivesmatter.info</a> for assistance.';
                    }
                }
                else
                {
                    // Display an error message if the password is not valid
                    $password_err = 'Incorrect password entered.';
                }
            }
            else
            {
                // Display an error message if username doesn't exist
                $username_err = 'No account could be found with that username.';
            }
        }
    }



    ////////////////////////////////////////////////////////////////////////////////
    // Form content

    echo '<h2>Login</h2>';
    echo '<p>Please enter your credentials to login.</p><br>';

    echo "<form action='$form_action_url' method='post'>";


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


    // Login button
    echo   '<div class="clearfix">';
    echo     '<div class="grid_2"></div>';
    echo     '<div class="grid_10">';
    echo       '<input type="submit" class="button-blue" value="Login" />';
    echo       '<br>&nbsp;&nbsp;Don\'t have an account? <a href="/account/register">Sign up now</a>.</span>';
    echo     '</div>';

    echo     '<div class="grid_3"></div>';
    echo     '<div class="grid_9">';
    echo     '</div>';
    echo   '</div>';


    echo '</form>';

?>
