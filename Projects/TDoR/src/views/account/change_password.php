<?php
    require_once('models/users.php');


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
        $form_action_url = '/account/change_password';

        $password_changed = false;
    
        $username = get_logged_in_username();

        $db             = new db_credentials();

        $users_table    = new Users($db);
        $user           = $users_table->get_user($username);

        if ($user->username === $username)
        {
            // Define variables and initialize with empty values
            $old_password = $new_password = $confirm_password = '';
            $old_password_err = $new_password_err = $confirm_password_err = $password_change_err = '';

            // Processing form data when form is submitted
            if ($_SERVER["REQUEST_METHOD"] == 'POST')
            {
                // Validate old password
                if (empty(trim($_POST['old_password']) ) )
                {
                    $old_password_err = 'Please enter your existing password.';
                }
                else
                {
                    $old_password = trim($_POST['old_password']);

                    $temp = password_hash($old_password, PASSWORD_DEFAULT);

                    if (!password_verify($old_password, $user->hashed_password) )
                    {
                        $old_password_err = 'Incorrect password entered.';
                    }
                }

                // Validate new password
                if (empty(trim($_POST['new_password']) ) )
                {
                    $new_password_err = 'Please enter the new password.';
                }
                elseif (strlen(trim($_POST['new_password']) ) < 6)
                {
                    $new_password_err = 'The new password must have at least 6 characters.';
                }
                else
                {
                    $new_password = trim($_POST['new_password']);
                }

                // Validate confirm password
                if (empty(trim($_POST['confirm_password']) ) )
                {
                    $confirm_password_err = 'Please confirm the password.';
                }
                else
                {
                    $confirm_password = trim($_POST['confirm_password']);
                
                    if (empty($new_password_err) && ($new_password != $confirm_password) )
                    {
                        $confirm_password_err = 'The passwords did not match.';
                    }
                }

                // Check input errors before updating the database
                if (empty($old_password_err) && empty($new_password_err) && empty($confirm_password_err) )
                {
                    $user->hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                    if ($users_table->update_user($user) )
                    {
                        // Password updated successfully.
                        $password_changed = true;
                    }
                    else
                    {
                        $password_change_err = 'Oops! Something went wrong. Please try again.';
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
            echo '<p>Please fill out this form to change your password.</p>';
            echo "<form action='$form_action_url' method='post'>";


            // Old password
            echo   '<div class="clearfix">';
            echo     '<div class="grid_2">';
            echo       '<label>Old Password</label>';
            echo     '</div>';

            echo     '<div class="grid_10">';
            echo       "<input type='password' name='old_password' value='$old_password' />";

            if (!empty($old_password_err) )
            {
                echo   "<p class='account-error'>$old_password_err</p>";
            }
            echo     '</div>';
            echo   '</div>';


            // New password
            echo   '<div class="clearfix">';
            echo     '<div class="grid_2">';
            echo       '<label>New Password</label>';
            echo     '</div>';

            echo     '<div class="grid_10">';
            echo       "<input type='password' name='new_password' value='$new_password' />";

            if (!empty($new_password_err) )
            {
                echo   "<p class='account-error'>$new_password_err</p>";
            }
            echo     '</div>';
            echo   '</div>';


            // Confirm new password
            echo   '<div class="clearfix">';
            echo     '<div class="grid_2">';
            echo       '<label>Confirm Password</label>';
            echo     '</div>';

            echo     '<div class="grid_10">';
            echo       '<input type="password" name="confirm_password" />';

            if (!empty($confirm_password_err) )
            {
                echo   "<p class='account-error'>$confirm_password_err</p>";
            }

            echo     '</div>';
            echo   '</div>';



            echo   '<div class="clearfix">';
            echo     '<div class="grid_2" ></div>';
            echo     '<div class="grid_10">';
            echo       '<input type="submit" class="button-blue" value="Submit" />';
            echo       '<a class="button-gray" href="/account">Cancel</a>';

            if (!empty($password_change_err) )
            {
                echo   "<p class='account-error'>$password_change_err</p>";
            }
            echo     '</div>';
            echo   '</div>';
            echo '</form>';
        }
    }

?>
