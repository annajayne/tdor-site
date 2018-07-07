<?php
    // Include config file
    require_once 'config.php';

    // Define variables and initialize with empty values
    $username = $password = $confirm_password = "";
    $username_err = $password_err = $confirm_password_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // Validate username
        $username = trim($_POST["username"]);

        if (empty($username) )
        {
            $username_err = "Please enter a username.";
        }
        else
        {
            // Prepare a select statement
            $sql = "SELECT id FROM users WHERE username = :username";

            if ($stmt = $pdo->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',   $param_username,    PDO::PARAM_STR);

                // Set parameters
                $param_username = trim($_POST["username"]);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() == 1)
                    {
                        $username_err = "Sorry! This username is already taken.";
                    }
                    else
                    {
                        $username = trim($_POST["username"]);
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
                $confirm_password_err = 'Password did not match.';
            }
        }

        // Check input errors before inserting in database
        if (empty($username_err) && empty($password_err) && empty($confirm_password_err) )
        {
            // Prepare an insert statement
            $sql = "INSERT INTO users (username, password, activated, created_at) VALUES (:username, :password, :activated, :created_at)";

            if ($stmt = $pdo->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',   $param_username,    PDO::PARAM_STR);
                $stmt->bindParam(':password',   $param_password,    PDO::PARAM_STR);
                $stmt->bindParam(':activated',  $param_activated,   PDO::PARAM_INT);
                $stmt->bindParam(':created_at', $param_created_at,  PDO::PARAM_STR);

                // Set parameters
                $param_username     = $username;
                $param_password     = password_hash($password, PASSWORD_DEFAULT);   // Creates a password hash
                $param_activated    = 0;                                            // The new user will have to be activated before they can login.
                $param_created_at   = date("Y-m-d H:i:s", time() );

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    // Redirect to login page
                    header("location: login.php");
                }
                else
                {
                    echo "Something went wrong. Please try again later.";
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