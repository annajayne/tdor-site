<?php
    /**
     * Login form
     *
     */


    /**
     * Show the login form.
     *
     * @param string            $form_action_url    The form URL
     * @param account_params    $params             Parameters for the form.
     */
    function show_login_form($form_action_url, $params)
    {
        echo '<p>Please enter your credentials to login.</p><br>';

        echo "<form action='$form_action_url' method='post'>";

        // Username
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2">';
        echo       '<label>Username or email address:</label>';
        echo     '</div>';

        echo     '<div class="grid_10">';
        echo       "<input type='text' name='username' value='$params->username' />";

        if (!empty($params->username_err) )
        {
            echo   "<p class='account-error'>$params->username_err</p>";
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

        if (!empty($params->password_err) )
        {
            echo   "<p class='account-error'>$params->password_err</p>";
        }
        echo     '</div>';
        echo   '</div>';

        // Login button
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2"></div>';
        echo     '<div class="grid_10">';
        echo       '<p><a href="/account/reset_password">Forgotten your login details?</a></p>';
        echo       '<input type="submit" class="button-blue" value="Login" />';
        echo       '<br>&nbsp;&nbsp;Don\'t have an account? <a href="/account/register">Sign up now</a>.</span>';
        echo     '</div>';

        echo     '<div class="grid_3"></div>';
        echo     '<div class="grid_9">';
        echo     '</div>';
        echo   '</div>';

        echo '</form>';
    }
?>