<?php
    /**
     * Register account page
     *
     */

    require_once('models/users.php');
    require_once('util/email_notifier.php');



    class registration_params
    {
        /** @var string                     The username. */
        public  $username;

        /** @var string                     Details of any error in the username. */
        public  $username_err;

        /** @var string                     The email address. */
        public  $email;

        /** @var string                     Details of any error in the email address. */
        public  $email_err;

        /** @var string                     The password. Must meet complexity requirements defined by is_password_valid() [account_utils.php]. */
        public  $password;

        /** @var string                     Details of any error in the password. */
        public  $password_err;

        /** @var string                     The password confirmation. Must match $password. */
        public  $confirm_password;

        /** @var string                     Details of any error in the password confirmation. */
        public  $confirm_password_err;


        /**
         * Constructor
         *
         */
        public function __construct()
        {
            $this->username             = '';
            $this->email                = '';
            $this->password             = '';
            $this->confirm_password     = '';

            $this->username_err         = '';
            $this->email_err            = '';
            $this->password_err         = '';
            $this->confirm_password_err = "";
        }

    }



    /**
     * Show the registration form.
     *
     * @param registration_params   $params        Parameters for the registration form.
     */
    function show_registration_form($params)
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

        $form_action_url    = '/account/register';
        
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
        echo       '<br>&nbsp;&nbsp;If you already have an account, you can <a href="/account/login"><b>login here</b></a>.';
        echo     '</div>';
        echo   '</div>';

        echo '</form>';
    }


    $show_form = true;

    // Define and initialise parameters
    $params = new registration_params();

    require_once('util/email_notifier.php');


    echo '<h2>Sign Up</h2>';

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $db                 = new db_credentials();
        $users_table        = new Users($db);

        // Validate username
        $params->username   = trim($_POST["username"]);
        $params->email      = trim($_POST["email"]);

        if (is_bot(get_user_agent() ) )
        {
            $params->email_err = 'Sorry, it looks like you might be a bot. If we are wrong about this please let us know';
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
                $params->email_err = "This email address is already registered. Please <a href='/account/login'>login here</a>";
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
                $params->username_err = "Sorry! This username is already taken";
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
            $params->confirm_password_err = 'Please confirm your password';
        }
        else
        {
            if ($params->password != $params->confirm_password)
            {
                $params->confirm_password_err = 'Passwords did not match';
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
                echo "Unfortunately something went wrong and your account could not be created. Please <a href='/account/register'>try again</a> or <a href='/pages/contact'>contact us</a> for assistance.";
            }
        }
    }


    if ($show_form)
    {
        show_registration_form($params);
    }

?>
