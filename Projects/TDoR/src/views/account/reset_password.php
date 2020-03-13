<?php
    /**
     * Reset password page
     *
     */

    require_once('models/users.php');
    require_once('util/email_notifier.php');
    require_once('views/account/forms/reset_password_request_form.php');


    echo '<h2>Reset Password</h2>';

    if (is_bot(get_user_agent() ) )
    {
        echo "<p class='account-error'>Sorry, it looks like you might be a bot. If we are wrong about this please let us know.</p>";
    }
    else
    {
        $show_form = true;

        $params = new account_params();

        if ($_SERVER["REQUEST_METHOD"] == "POST")
        {
            $db                 = new db_credentials();
            $users_table        = new Users($db);

            // Validate the entered username
            $params->username   = trim($_POST["username"]);
            $params->email      = trim($_POST["email"]);

            $user = null;

            if (!empty($params->username) )
            {
                $user = $users_table->get_user($params->username);

                if ($user === null)
                {
                    $params->username_err = "This username is not registered. Did you misspell it?";
                }
            }
            else
            {
                if (!empty($params->email) )
                {
                    if (filter_var($params->email, FILTER_VALIDATE_EMAIL) )
                    {
                        $user = $users_table->get_user_from_email_address($params->email);
                        if ($user === null)
                        {
                            $params->email_err = "This email address is not registered. Did you misspell it?";
                        }
                    }
                    else
                    {
                        $params->email_err = "Please enter a valid email address";
                    }
                }
            }

            if ($user != null)
            {
                $show_form = false;

                // Write a password reset ID to the users table
                $user->password_reset_id            = $users_table->generate_api_key($user);
                $user->password_reset_timestamp     = date("Y-m-d H:i:s", time() );

                if ($users_table->update_user($user) )
                {
                    // Send a link to the user
                    $notifier = new EmailNotifier();

                    $confirmation_url = $notifier->send_user_account_password_reset($user);

                    echo "<p>A confirmation email has been sent to <b>$user->email</b>.</p>";
                    if (DEV_INSTALL)
                    {
                        echo "<p>[Debug] Confirmation URL: <a href='$confirmation_url'>$confirmation_url</a></p>";
                    }
                    echo '<p><b>Please click on the link in the email to reset your password.</b> The link will remain valid for 24 hours.</p><br>';
                }
                else
                {
                    redirect_to('/pages/error');
                }
            }
        }

        if ($show_form)
        {
            $form_action_url = '/account/reset_password';

            show_reset_password_request_form($form_action_url, $params);
        }
    }
?>
