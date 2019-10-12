<?php
    require_once 'config.php';
    require_once './../defines.php';
    require_once './../misc.php';


    // Define variables and initialize with empty values
    $username = $email = $password = $confirm_password = "";
    $username_err = $email_err = $password_err = $confirm_password_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // Validate username
        $username = trim($_POST["username"]);
        $email    = trim($_POST["email"]);

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) )
        {
            $email_err = "Please enter a valid email address.";
        }
        else
        {
            $sql = "SELECT id FROM users WHERE email = :email";

            if ($stmt = $pdo->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':email',   $param_email,    PDO::PARAM_STR);

                // Set parameters
                $param_email = trim($_POST["email"]);

                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    if ($stmt->rowCount() == 1)
                    {
                        $email_err = "Sorry! This email address is already taken.";
                    }
                    else
                    {
                        $email = trim($_POST["email"]);
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
        if (empty($username_err) &&empty($email_err) && empty($password_err) && empty($confirm_password_err) )
        {
            $user_count = 0;

            // Is this the first user? If so, we need to make them an admin and activate automatically
            if ( ($stmt_count = $pdo->prepare("SELECT count(id) FROM users") ) && $stmt_count->execute() )
            {
                if ($stmt_count->rowCount() == 1)
                {
                    if ($row = $stmt_count->fetch() )
                    {
                        $user_count    = (int)$row[0];
                    }
                }
            }

            // Prepare an insert statement
            $sql = "INSERT INTO users (username, email, password, roles, activated, created_at) VALUES (:username, :email, :password, :roles, :activated, :created_at)";

            if ($stmt = $pdo->prepare($sql) )
            {
                // Bind variables to the prepared statement as parameters
                $stmt->bindParam(':username',   $param_username,    PDO::PARAM_STR);
                $stmt->bindParam(':email',      $param_email,       PDO::PARAM_STR);
                $stmt->bindParam(':password',   $param_password,    PDO::PARAM_STR);
                $stmt->bindParam(':roles',      $param_roles,       PDO::PARAM_STR);
                $stmt->bindParam(':activated',  $param_activated,   PDO::PARAM_INT);
                $stmt->bindParam(':created_at', $param_created_at,  PDO::PARAM_STR);

                // Set parameters
                $param_username     = $username;
                $param_email        = $email;
                $param_password     = password_hash($password, PASSWORD_DEFAULT);   // Creates a password hash
                $param_roles        = 'I';                                          // Default role = API
                $param_activated    = 0;                                            // The new user will have to be activated before they can login.
                $param_created_at   = date("Y-m-d H:i:s", time() );

                if ($user_count === 0)
                {
                    // This is the first user, so activate automatically and make them an admin    
                    $param_roles        .= 'EA';
                    $param_activated    = 1;
                }
                // Attempt to execute the prepared statement
                if ($stmt->execute() )
                {
                    // Notify the admin that a user has registered
                    $host       = raw_get_host();
                    $subject    = "New user registered on $host";
                    $html       = "<p>The user (<b>$param_username</b>) has just registered on $host.</p><p>&nbsp;</p><p><a href='$host/pages/admin?target=users'><b>Administer Users</b></a></p>";

                    send_email(ADMIN_EMAIL_ADDRESS, NOTIFY_EMAIL_ADDRESS, $subject, $html);

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