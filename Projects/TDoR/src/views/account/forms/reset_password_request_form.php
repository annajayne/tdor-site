<?php
    /**
     * Reset password request form
     *
     */


    /**
     * Show the reset password request form.
     *
     * @param string            $form_action_url    The form URL
     * @param account_params    $params             Parameters for the registration form.
     */
    function show_reset_password_request_form($form_action_url, $params)
    {
        echo '<p>To reset your password please enter either your username <i>or</i> email address below:</p>';
        echo "<form action='$form_action_url' method='post'>";

        // Username
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2">';
        echo       '<label>Username:</label>';
        echo     '</div>';

        echo     '<div class="grid_10">';
        echo       "<input type='text' name='username' id='username' value='$params->username' />";

        if (!empty($params->username_err) )
        {
            echo   "<p class='account-error'>$params->username_err</p>";
        }
        echo     '</div>';
        echo   '</div>';


        // Email
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2">';
        echo       '<label>Email:</label>';
        echo     '</div>';

        echo     '<div class="grid_10">';
        echo       "<input type='text' name='email' id='email' value='$params->email' />";

        if (!empty($params->email_err) )
        {
            echo   "<p class='account-error'>$params->email_err</p>";
        }
        echo     '</div>';
        echo   '</div>';

        // Reset button
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2"></div>';
        echo     '<div class="grid_10">';
        echo       '<input type="submit" class="button-dkred" value="Reset your password" />';
        echo     '</div>';

        echo     '<div class="grid_3"></div>';
        echo     '<div class="grid_9">';
        echo     '</div>';
        echo   '</div>';

        echo '</form>';
    }

?>