<?php
    require_once 'config.php';
    require_once './../defines.php';
    require_once './../misc.php';
    require_once('./../utils.php');
    require_once('./../db_utils.php');
    require_once('./../models/users.php');


    // Define variables and initialize with empty values
    $username = $email = $password = $confirm_password = "";
    $username_err = $email_err = $password_err = $confirm_password_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $db             = new db_credentials();

        $users_table    = new Users($db);

        // Validate username
        $username = trim($_POST["username"]);
        $email    = trim($_POST["email"]);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) )
        {
            $email_err = "Please enter a valid email address.";
        }
        else
        {
            $user           = $users_table->get_user_from_email_address($email);

            if (!empty($user->username) )
            {
                $email_err = "Sorry! This email address is already taken.";
            }
        }

        if (empty($username) )
        {
            $username_err = "Please enter a username.";
        }
        else
        {
            $user = $users_table->get_user($username);

            if (!empty($user->username) )
            {
                $username_err = "Sorry! This username is already taken.";
            }
        }

        // Validate password
        $password = trim($_POST['password']);

        if (empty($password) )
        {
            $password_err = "Please enter a password.";
        }
        elseif (strlen($password) < 10)
        {
            $password_err = "Password must be at least 10 characters long.";
        }

        // Validate confirm password
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($confirm_password) )
        {
            $confirm_password_err = 'Please confirm password.';
        }
        else
        {
            if ($password != $confirm_password)
            {
                $confirm_password_err = 'Passwords did not match.';
            }
        }

        // Check input errors before inserting in database
        if (empty($username_err) &&empty($email_err) && empty($password_err) && empty($confirm_password_err) )
        {
            // Is this the first user? If so, we need to make them an admin and activate automatically
            $user_count = count($users_table->get_all() );

            $user = new User;

            $user->username         = $username;
            $user->email            = $email;
            $user->hashed_password  = password_hash($password, PASSWORD_DEFAULT);   // Creates a password hash

            $user->roles            = 'I';                                          // Default role = API user
            $user->api_key          = $users_table->generate_api_key(user);
            $user->activated        = 0;                                            // The new user will have to be activated before they can login.
            $user->created_at       = date("Y-m-d H:i:s", time() );

            if ($user_count === 0)
            {
                // This is the first user, so activate automatically and make them an admin    
                $user->roles       .= 'EA';
                $user->activated    = 1;
            }

            if ($users_table->add_user($user) )
            {
                // Notify the admin that a user has registered
                $host       = raw_get_host();
                $subject    = "New user registered on $host";
                $html       = "<p>The user <b>$user->username</b> ($user->email) has just registered on $host.</p><p>&nbsp;</p><p><a href='$host/pages/admin?target=users'><b>Administer Users</b></a></p>";

                send_email(ADMIN_EMAIL_ADDRESS, NOTIFY_EMAIL_ADDRESS, $subject, $html);

                // Redirect to login page
                header("location: login.php");
            }
            else
            {
                echo "Something went wrong. Please try again later.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css" />
    <style type="text/css">
        body {
            font: 14px sans-serif;
        }

        .wrapper {
            width: 350px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php
                      echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
            method="post">
            <div class="form-group <?php
                                   echo (!empty($email_err) ) ? 'has-error' : ''; ?>">
                <label>Email</label>
                <input type="text" name="email" class="form-control" value="<?php
                                                                               echo $email; ?>" />
                <span class="help-block">
                    <?php
                    echo $email_err; ?>
                </span>
            </div>
            <div class="form-group <?php
                                   echo (!empty($username_err) ) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php
                                                                               echo $username; ?>" />
                <span class="help-block">
                    <?php
                    echo $username_err; ?>
                </span>
            </div>
            <div class="form-group <?php
                                   echo (!empty($password_err) ) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php
                                                                                   echo $password; ?>" />
                <span class="help-block">
                    <?php
                    echo $password_err; ?>
                </span>
            </div>
            <div class="form-group <?php
                                   echo (!empty($confirm_password_err) ) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php
                                                                                           echo $confirm_password; ?>" />
                <span class="help-block">
                    <?php
                    echo $confirm_password_err; ?>
                </span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit" />
                <input type="reset" class="btn btn-default" value="Reset" />
            </div>
            <p>
                Already have an account?
                <a href="login.php">Login here</a>.
            </p>
        </form>
    </div>
</body>
</html>