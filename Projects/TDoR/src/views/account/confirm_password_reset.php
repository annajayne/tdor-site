<?php
   /**
     * Confirm password reset page
     *
     */

    require_once('models/users.php');
    require_once('views/account/forms/reset_password_form.php');


    // Check if the user is logged in - if so redirect to the login page
    if (is_logged_in() && redirect_to('/account/login') )
    {
        exit;
    }

    echo '<h2>Reset Password</h2>';

    $password_reset_id = (isset($_GET['id']) ) ? $_GET['id'] : '';

    $form_action_url = "/account/confirm_password_reset?id=$password_reset_id";

    $password_reset = false;
    
    $db             = new db_credentials();

    $users_table    = new Users($db);
    $user           = $users_table->get_user_from_password_reset_id($password_reset_id);

    $params         = new account_params;

    if (!empty($user->username) )
    {
        // Processing form data when form is submitted
        if ($_SERVER["REQUEST_METHOD"] == 'POST')
        {
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
                
                if ($params->new_password != $params->confirm_password)
                {
                    $params->confirm_password_err = 'The passwords did not match.';
                }
            }

            // Check input errors before updating the database
            if (empty($params->new_password_err) && empty($params->confirm_password_err) )
            {
                $user->hashed_password      = password_hash($params->new_password, PASSWORD_DEFAULT);
                $user->password_reset_id    = '';

                if ($users_table->update_user($user) )
                {
                    // Password updated successfully.
                    $password_reset = true;
                }
                else
                {
                    $params->password_change_err = 'Oops! Something went wrong. Please try again.';
                }
            }
        }


        if ($password_reset)
        {
            echo '<p>Your password has been reset.</p>';
            echo '<p><a href="/account"><b>Click here to login</b></a>.</p>';
        }
        else if (!empty($password_reset_id) )
        {
            if ($user->is_password_reset_still_valid() )
            {
                show_password_reset_form($form_action_url, $params);
            }
            else
            {
                // The password reset ID has expired - allow the user to request another one.
                redirect_to('/account/reset_password');
            }
        }
    }
    else
    {
        // User account or password reset id not recognised.
        redirect_to('/pages/error');
    }

?>
