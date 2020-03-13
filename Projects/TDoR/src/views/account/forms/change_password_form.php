<?php
    /**
     * Change password form
     *
     */


    /**
     * Show the change password form.
     *
     * @param string            $form_action_url    The form URL
     * @param account_params    $params             Parameters for the form.
     */
    function show_change_password_form($form_action_url, $params)
    {
        echo '<p>Please fill out this form to change your password.</p>';
        echo "<form action='$form_action_url' method='post'>";

        // Old password
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2">';
        echo       '<label>Old Password</label>';
        echo     '</div>';

        echo     '<div class="grid_10">';
        echo       "<input type='password' name='password' value='$params->password' />";

        if (!empty($params->password_err) )
        {
            echo   "<p class='account-error'>$params->password_err</p>";
        }
        echo     '</div>';
        echo   '</div>';

        // New password
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2">';
        echo       '<label>New Password</label>';
        echo     '</div>';

        echo     '<div class="grid_10">';
        echo       "<input type='password' name='new_password' value='$params->new_password' />";

        if (!empty($params->new_password_err) )
        {
            echo   "<p class='account-error'>$params->new_password_err</p>";
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

        if (!empty($params->confirm_password_err) )
        {
            echo   "<p class='account-error'>$params->confirm_password_err</p>";
        }
        echo     '</div>';
        echo   '</div>';

        echo   '<div class="clearfix">';
        echo     '<div class="grid_2" ></div>';
        echo     '<div class="grid_10">';
        echo       '<input type="submit" class="button-blue" value="Submit" />';
        echo       '<a class="button-gray" href="/account">Cancel</a>';

        if (!empty($params->password_change_err) )
        {
            echo   "<p class='account-error'>$params->password_change_err</p>";
        }
        echo     '</div>';
        echo   '</div>';
        echo '</form>';
    }

?>