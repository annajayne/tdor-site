<?php 
    // Page layout
    //
    // Contains only:
    //  1.  A homepage link
    //  2.  A "Posts" link. Click on this to see a list of posts in the database
    //
    // routes.php takes care of working out what we need to display based on the $controller and $action variables in the top level index.php file.
?>

<DOCTYPE html>
<html>
  <head>
  </head>

  <body>
    <header>
      <a href='/php_mvc_blog'>Home</a>
      <a href='?controller=posts&action=index'>Posts</a>
    </header>

    <?php require_once('routes.php'); ?>

    <footer>
        &lt;<i>Copyright footer type stuff goes here</i>&gt;
    </footer>

  </body>
</html>
