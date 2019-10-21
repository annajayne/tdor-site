<?php
    // Login system based on the tutorial at https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php
    //
    require_once('account_utils.php');


    // Initialise the session
    session_start();

    if (!is_logged_in() )
    {
        header("location: /account/login.php");
        exit;
    }
    
    $is_editor = is_editor_user();
    $is_admin  = is_admin_user();

    $roles = '';
    if ($is_editor)
    {
        $roles = 'Editor; ';
    }

    if ($is_admin)
    {
        $roles .= 'Admin; ';
    }
    
    $roles = rtrim($roles, '; ');
?>
 
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body { font: 14px sans-serif; text-align: center; }
    </style>
  </head>
  <body>
    <div class="page-header">
        <?php echo '<h1>Welcome, <b>'.htmlspecialchars($_SESSION['username']).'</b> ('.$roles.') </h1>'; ?>
    </div>
    <p>
      <a href="./../" class="btn btn-info">Homepage</a>&nbsp;
      <a href="./../reports" class="btn btn-info">Reports</a>&nbsp;
      <a href="/account/logout.php" class="btn btn-danger">Logout</a>
    </p>
  </body>
</html>