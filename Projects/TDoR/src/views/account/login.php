<?php
    /**
     * Login account page
     *
     */

    require_once('util/utils.php');
    require_once('models/users.php');
    require_once('views/account/forms/login_form.php');


    echo '<h2>Login</h2>';

    $params = new account_params();

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $params->username = trim($_POST["username"]);
        $params->password = trim($_POST['password']);

        if (is_bot(get_user_agent() ) )
        {
            $params->username_err = 'Sorry, it looks like you might be a bot. If we are wrong about that please let us know';
        }

        // Check if username is empty
        if (empty($params->username) )
        {
            $params->username_err = 'Please enter your username or email address.';
        }

        // Check if password is empty
        if (empty($params->password) )
        {
            $params->password_err = 'Please enter your password.';
        }

        // Validate credentials
        if (empty($params->username_err) && empty($params->password_err) )
        {
            $db             = new db_credentials();

            $users_table    = new Users($db);
            $user           = $users_table->get_user($params->username);

            if (empty($user->username) )
            {
                // if the username isn't found, check in case it's an email address
                $user       = $users_table->get_user_from_email_address($params->username);
            }

            if (!empty($user->username) )
            {
                // The username exists
                if (password_verify($params->password, $user->hashed_password) )
                {
                    if (empty($user->confirmation_id) )
                    {
                        if ($user->activated)
                        {
                            if (empty($user->api_key) )
                            {
                                // If an API key has not yet been generated, generate and store one now
                                $user->api_key = $users_table->generate_api_key($user);
                            }

                            $user->last_login       = date("Y-m-d H:i:s", time() );

                            $users_table->update_user($user);

                            // The password is correct and the account is active, so store copies of the relevant
                            // user properties in the session and redirect to the welcome page
                            $_SESSION               = array();

                            $_SESSION['username']   = $user->username;
                            $_SESSION['email']      = $user->email;
                            $_SESSION['roles']      = $user->roles;
                            $_SESSION['api_key']    = $user->api_key;

                            $redirect_url = '/account';

                            if (isset($_GET['url']) )
                            {
                                $redirect_url = $redirect_url.'?url='.urlencode($_GET['url']);
                            }

                            if (redirect_to($redirect_url))
                            {
                                exit;
                            }
                        }
                        else
                        {
                            $params->password_err = 'This account has not yet been activated. Please contact <a href="mailto:tdor@translivesmatter.info">tdor@translivesmatter.info</a> for assistance.';
                        }
                    }
                    else
                    {
                        // TODO add a "resend email" link.
                        $params->password_err = 'Please check your email and confirm your account registration before attempting to login.';
                    }
                }
                else
                {
                    // Display an error message if the password is not valid
                    $params->password_err = 'Incorrect password entered.';
                }
            }
            else
            {
                // Display an error message if username doesn't exist
                $params->username_err = 'No account could be found with that username.';
            }
        }
    }

    $form_action_url = '/account/login';

    if (isset($_GET['url']) )
    {
        $form_action_url = $form_action_url.'?url='.urlencode($_GET['url']);
    }

    show_login_form($form_action_url, $params);
?>
