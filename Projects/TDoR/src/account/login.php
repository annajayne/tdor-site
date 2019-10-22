<?php
    // Include config file
    require_once 'config.php';
    require_once('../db_credentials.php');
    require_once('../misc.php');
    require_once('../utils.php');
    require_once('../db_utils.php');
    require_once('../models/users.php');


    // Avoids: Warning: Unknown: Your script possibly relies on a session side-effect which existed until PHP 4.2.3. Please be advised that the session extension does not consider global variables as a source of data, unless register_globals is enabled. You can disable this functionality and this warning by setting session.bug_compat_42 or session.bug_compat_warn to off, respectively in Unknown on line 0
    // ref: https://stackoverflow.com/questions/175091/php-session-side-effect-warning-with-global-variables-as-a-source-of-data
    ini_set('session.bug_compat_warn', 0);
    ini_set('session.bug_compat_42', 0);

    // Define variables and initialize with empty values
    $username = $password = "";
    $username_err = $password_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $username = trim($_POST["username"]);
        $password = trim($_POST['password']);

        // Check if username is empty
        if (empty($username) )
        {
            $username_err = 'Please enter your username.';
        }

        // Check if password is empty
        if (empty($password) )
        {
            $password_err = 'Please enter your password.';
        }

        // Validate credentials
        if (empty($username_err) && empty($password_err) )
        {
            $db             = new db_credentials();

            $users_table    = new Users($db);
            $user           = $users_table->get_user($username);

            if (!empty($user->username) )
            {
                // The username exists
                if (password_verify($password, $user->hashed_password) )
                {
                    if ($user->activated)
                    {
                        if (empty($user->api_key) )
                        {
                            // If an API key has not yet been generated, generate and store one now
                            $user->api_key = $users_table->generate_api_key($user);

                            $users_table->update_user($user);
                        }

                        // The password is correct and the account is active, so start a new session
                        // and store copies of the relevant user properties in the session
                        session_start();

                        $_SESSION['username']   = $user->username;
                        $_SESSION['roles']      = $user->roles;
                        $_SESSION['api_key']    = $user->api_key;

                        header("location: welcome.php");
                    }
                    else
                    {
                        $password_err = 'This account has not yet been activated. Please contact <a href="mailto:tdor@translivesmatter.info">tdor@translivesmatter.info</a> for assistance.';
                    }
                }
                else
                {
                    // Display an error message if the password is not valid
                    $password_err = 'The password you entered was not valid.';
                }
            }
            else
            {
                // Display an error message if username doesn't exist
                $username_err = 'No account could be found with that username.';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css" />
    <style type="text/css">
        body
        {
            font: 14px sans-serif;
        }

        .wrapper
        {
            width: 350px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"
            method="post">
            <div class="form-group <?php echo (!empty($username_err) ) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" />
                <span class="help-block">
                    <?php echo $username_err; ?>
                </span>
            </div>
            <div class="form-group <?php echo (!empty($password_err) ) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" />
                <span class="help-block">
                    <?php echo $password_err; ?>
                </span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login" />
            </div>
            <p>
                Don't have an account?
                <a href="register.php">Sign up now</a>.
            </p>
        </form>
    </div>
</body>
</html>