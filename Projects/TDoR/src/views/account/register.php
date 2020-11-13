<?php
    /**
     * Register account page
     *
     */

    require_once('utils.php');              // For get_config() and verify_recaptcha_v2
    require_once('models/users.php');
    require_once('util/email_notifier.php');
    require_once('views/account/forms/registration_form.php');


    // Scripts
    echo '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';


    // Define and initialise parameters
    $site_config        = get_config();
    $show_form          = true;
    $form_action_url    = '/account/register';

    $params = new account_params();


    echo '<h2>Register</h2>';

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $captcha_response   = $_POST['g-recaptcha-response'];

        $captcha_ok         = !empty($captcha_response);

        if ($captcha_ok)
        {
            // Verify the captcha - see https://www.kaplankomputing.com/blog/tutorials/recaptcha-php-demo-tutorial/
            $secret_key = $site_config['reCaptcha']['secret_key'];

            $captcha_ok = verify_recaptcha_v2($captcha_response, $secret_key);
        }

        $db                 = new db_credentials();
        $users_table        = new Users($db);

        // Validate username
        $params->username   = trim($_POST["username"]);
        $params->email      = trim($_POST["email"]);

        if (!$captcha_ok)
        {
            $params->confirm_password_err = 'Please complete the captcha below.';
        }
        else if (is_bot(get_user_agent() ) )
        {
            $params->email_err = 'Sorry, it looks like you might be a bot. If we are wrong about this please let us know.';
        }
        else if (empty($params->email) || !filter_var($params->email, FILTER_VALIDATE_EMAIL) )
        {
            $params->email_err = "Please enter a valid email address";
        }
        else
        {
            $user = $users_table->get_user_from_email_address($params->email);

            if (!empty($user->username) )
            {
                $params->email_err = "This email address is already registered. Please <a href='/account/login'>login here</a>.";
            }
        }

        if (empty($params->username) )
        {
            $params->username_err = "Please enter a username.";
        }
        else
        {
            $user = $users_table->get_user($params->username);

            if (!empty($user->username) )
            {
                $params->username_err = "Sorry! This username is already taken.";
            }
        }

        // Validate password
        $params->password = trim($_POST['password']);

        if (empty($params->password) )
        {
            $params->password_err = "Please enter a password";
        }
        else
        {
            if (!is_password_valid($params->password) )
            {
                $params->password_err = get_password_validity_msg();
            }
        }

        // Validate confirm password
        $params->confirm_password = trim($_POST['confirm_password']);

        if (empty($params->confirm_password) )
        {
            $params->confirm_password_err = 'Please confirm your password.';
        }
        else
        {
            if ($params->password != $params->confirm_password)
            {
                $params->confirm_password_err = 'Passwords did not match.';
            }
        }

        // Check input errors before inserting in database
        if (empty($params->username_err) && empty($params->email_err) && empty($params->password_err) && empty($params->confirm_password_err) )
        {
            // Validation passed, so we don't need to show the form this time.
            $show_form = false;

            // Is this the first user? If so, we need to make them an admin and activate automatically
            $user_count = count($users_table->get_all() );

            $user = new User;

            $user->username         = $params->username;
            $user->email            = $params->email;
            $user->hashed_password  = password_hash($params->password, PASSWORD_DEFAULT);   // Creates a password hash

            $user->roles            = 'I';                                                  // Default role = API user
            $user->api_key          = $users_table->generate_api_key($user);                // API key
            $user->confirmation_id  = $users_table->generate_api_key($user);                // Confirmation key sent with the registration email
            $user->activated        = 0;                                                    // The new user will have to be activated before they can login.
            $user->created_at       = date("Y-m-d H:i:s", time() );

            if ($user_count === 0)
            {
                // This is the first user, so activate automatically and make them an admin
                $user->roles        = 'EA';
                $user->activated    = 1;
            }

            if ($users_table->add_user($user) )
            {
                $notifier = new EmailNotifier();

                // Send an email to the user to confirm the registration.
                // When the user clicks the link, mark the account as "confirmed" and clear the confirmation key (but it still needs to be activated)
                // The confirmation page should inform the user that they will receive an email once the account has been activated.
                $confirmation_url = $notifier->send_user_account_registration_confirmation($user);

                // Notify the admin that a new user has registered
                $notifier->notify_admin_account_created($user);

                echo "<p>A confirmation email has been sent to $user->email.</p><p><b>Please click on the link in the email to confirm your account.</b></p><br>";

                if (DEV_INSTALL)
                {
                    echo "[Debug] Confirmation URL: <a href='$confirmation_url'>$confirmation_url</a>";
                }
            }
            else
            {
                echo "Unfortunately something went wrong and your account could not be creaed. Please <a href='/account/register'>try again</a> or <a href='/pages/contact'>contact us</a> for assistance.";
            }
        }
    }

    if ($show_form)
    {
        show_registration_form($form_action_url, $site_config, $params);
    }

?>
