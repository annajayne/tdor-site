<?php
    require_once('models/users.php');
    require_once('views/account/forms/change_password_form.php');



    // Check if the user is logged in, otherwise redirect to login page
    if (!is_logged_in() )
    {
        if (redirect_to('/account/login') )
        {
            exit;
        }
    }
    else
    {
        $password_changed = false;
    
        $username = get_logged_in_username();

        $db             = new db_credentials();

        $users_table    = new Users($db);
        $user           = $users_table->get_user($username);

        if ($user->username === $username)
        {
            $params = new account_params;

            // Processing form data when form is submitted
            if ($_SERVER["REQUEST_METHOD"] == 'POST')
            {
                // Validate old password
                if (empty(trim($_POST['password']) ) )
                {
                    $params->password_err = 'Please enter your existing password.';
                }
                else
                {
                    $params->password = trim($_POST['password']);

                    $temp = password_hash($params->password, PASSWORD_DEFAULT);

                    if (!password_verify($params->password, $user->hashed_password) )
                    {
                        $params->password_err = 'Incorrect password entered.';
                    }
                }

                // Validate new password
                $params->new_password = trim($_POST['new_password']);

                if (empty($params->new_password) )
                {
                    $params->new_password_err = 'Please enter the new password.';
                }
                else
                {
                    if (!is_password_valid($params->new_password) )
                    {
                        $params->new_password_err = get_password_validity_msg();
                    }
                }

                // Validate confirm password
                if (empty(trim($_POST['confirm_password']) ) )
                {
                    $params->confirm_password_err = 'Please confirm the password.';
                }
                else
                {
                    $params->confirm_password = trim($_POST['confirm_password']);
                
                    if (empty($params->new_password_err) && ($params->new_password != $params->confirm_password) )
                    {
                        $params->confirm_password_err = 'The passwords did not match.';
                    }
                }

                // Check input errors before updating the database
                if (empty($params->password_err) && empty($params->new_password_err) && empty($params->confirm_password_err) )
                {
                    $user->hashed_password = password_hash($params->new_password, PASSWORD_DEFAULT);
                
                    if ($users_table->update_user($user) )
                    {
                        // Password updated successfully.
                        $password_changed = true;
                    }
                    else
                    {
                        $params->password_change_err = 'Oops! Something went wrong. Please try again.';
                    }
                }
            }
        }


        echo '<h2>Change Password</h2>';

        if ($password_changed)
        {
            echo '<p>&nbsp;</p>';
            echo '<p>Your password has been changed.</p>';
            echo '<p><a href="/account"><b>Click here to continue</b></a>.</p>';
        }
        else
        {
            $form_action_url = '/account/change_password';

            show_change_password_form($form_action_url, $params);
        }
    }

?>
