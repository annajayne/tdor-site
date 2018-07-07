<?php
    // Include config file
    require_once 'config.php';

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
            $username_err = 'Please enter username.';
        }

        // Check if password is empty
        if (empty($password) )
        {
            $password_err = 'Please enter your password.';
        }

        // Validate credentials
        if (empty($username_err) && empty($password_err) )
        {
            // Prepare a select statement
            $sql = "SELECT username, password, activated FROM users WHERE username = :username";

            if ($stmt = $pdo->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username', $param_username, PDO::PARAM_STR);

                // Set parameters
                $param_username = trim($_POST["username"]);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    // Check if username exists, if yes then verify password
                    if ($stmt->rowCount() == 1)
                    {
                        if ($row = $stmt->fetch() )
                        {
                            $hashed_password    = $row['password'];
                            $activated          = $row['activated'];

                            if (password_verify($password, $hashed_password) )
                            {
                                if ($activated)
                                {
                                    /* Password is correct and account activated, so start a new session and
                                    save the username to the session */
                                    session_start();

                                    $_SESSION['username'] = $username;

                                    header("location: welcome.php");
                                }
                                else
                                {
                                    $password_err = 'This account has not yet been activated. Please contact <a href="mailto:tdor@translivesmatter.info">tdor@translivesmatter.info</a> for assistance.';
                                }
                            }
                            else
                            {
                                // Display an error message if password is not valid
                                $password_err = 'The password you entered was not valid.';
                            }
                        }
                    }
                    else
                    {
                        // Display an error message if username doesn't exist
                        $username_err = 'No account found with that username.';
                    }
                }
                else
                {
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
            // Close statement
            unset($stmt);
        }
        // Close connection
        unset($pdo);
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