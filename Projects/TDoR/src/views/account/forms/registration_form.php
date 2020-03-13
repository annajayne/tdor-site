<?php
    /**
     * Registration form
     *
     */


    /**
     * Show the registration form.
     *
     * @param string            $form_action_url    The form URL
     * @param account_params    $params             Parameters for the registration form.
     */
    function show_registration_form($form_action_url, $params)
    {
?>
        <script>
            function email_changed()
            {
                const email_ctrl    = document.getElementById("email");
                const username_ctrl = document.getElementById("username");

                const email = email_ctrl.value;
                var username = username_ctrl.value;

                const pos = email.indexOf("@");

                if ( (pos >= 0) && (username.length == 0) )
                {
                    username = email.substr(0, pos);

                    username_ctrl.value = username;
                }
            }
        </script>
<?php
        ////////////////////////////////////////////////////////////////////////////////
        // Form content

        echo '<p>Please fill in the form below to create an account:</p><br>';

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
        echo       "<input type='text' name='email' id='email' onchange='javascript:email_changed()' value='$params->email' />";

        if (!empty($params->email_err) )
        {
            echo   "<p class='account-error'>$params->email_err</p>";
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


        // Password confirmation
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2">';
        echo       '<label>Confirm Password:</label>';
        echo     '</div>';

        echo     '<div class="grid_10">';
        echo       "<input type='password' name='confirm_password' value='$params->confirm_password' />";

        if (!empty($params->confirm_password_err) )
        {
            echo   "<p class='account-error'>$params->confirm_password_err</p>";
        }

        echo     '</div>';
        echo   '</div>';

 
        // Submit & Reset buttons
        echo   '<div class="clearfix">';
        echo     '<div class="grid_2"></div>';
        echo     '<div class="grid_10">';
        echo       '<input type="submit" class="button-blue" value="Submit" />&nbsp;';
        echo       '<input type="reset" class="button-gray" value="Reset" />';
        echo       '<br>&nbsp;&nbsp;If you already have an account, <a href="/account/login"><b>you can login here</b></a>.';
        echo     '</div>';
        echo   '</div>';

        echo '</form>';
    }

?>