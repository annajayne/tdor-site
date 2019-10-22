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
    
    $is_api_user    = is_api_user();
    $is_editor      = is_editor_user();
    $is_admin       = is_admin_user();

    $roles = '';
    if ($is_api_user)
    {
        $roles = 'API user; ';
    }

    if ($is_editor)
    {
        $roles .= 'Editor; ';
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
    <p>&nbsp;</p>
    <?php 
        $is_api_user    = is_api_user();

        if ($is_api_user && isset($_SESSION['api_key']) )
        {
            $api_key = $_SESSION['api_key'];

            echo "<h3>API key: $api_key</h3>";
        }
    ?>
    <p>&nbsp;</p>
    <p>
      <a href="./../" class="btn btn-info">Homepage</a>&nbsp;
      <a href="./../reports" class="btn btn-info">Reports</a>&nbsp;

      <?php 
        if (!$is_editor)
        {
            $subject = 'tdor.translivesmatter.info editor application';
            $body    = 'Hi folks,%0A%0AI am interested in becoming an editor for tdor.translivesmatter.info.%0A%0A';
            $body   .= '<Please tell us a little bit about yourself here, including any language, research or programming etc. skills you think might be relevant>%0A%0A';
            $body   .= '%0ASincerely,%0A%0A<Your name here. Please remember to include any contact details/social media handles you think appropriate>%0A%0A%0A%0A';

            $url = 'mailto:tdor@translivesmatter.info?subject='.urlencode($subject).'&body='.urlencode($body);
            $url = 'mailto:tdor@translivesmatter.info?subject='.$subject.'&body='.$body;

            echo "<a href='$url' class='btn btn-warning'>Apply to become an editor</a>";
        }
      ?>
      <a href="/account/logout.php" class="btn btn-danger">Logout</a>
    </p>
  </body>
</html>