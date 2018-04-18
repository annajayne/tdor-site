<?php 
    // Page layout
    //
    // Contains only:
    //  1.  A homepage link
    //  2.  A "Posts" link. Click on this to see a list of posts in the database
    //
    // routes.php takes care of working out what we need to display based on the $controller and $action variables in the top level index.php file.
?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Simple Responsive Template</title>
  <meta name="description" content="Simple Responsive Template is a template for responsive web design. Mobile first, responsive grid layout, toggle menu, navigation bar with unlimited drop downs, responsive slideshow">
  <meta name="keywords" content="">

  <!-- Mobile viewport -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">

  <link rel="shortcut icon" href="images/favicon.ico"  type="image/x-icon">

  <!-- CSS-->
  <!-- Google web fonts. You can get your own bundle at http://www.google.com/fonts. Don't forget to update the CSS accordingly!-->
  <link href='http://fonts.googleapis.com/css?family=Droid+Serif|Ubuntu' rel='stylesheet' type='text/css'>

  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="js/flexslider/flexslider.css">
  <link rel="stylesheet" href="css/basic-style.css">

  <!-- end CSS-->

  <!-- JS-->
  <script src="js/libs/modernizr-2.6.2.min.js"></script>
  <!-- end JS-->
</head>

<body id="home">
  <!-- header area -->
  <header class="wrapper clearfix">

    <div id="banner">        
      <div id="logo"><a href="basic.html"><img src="images/basic-logo.svg" alt="logo"></a></div> 
    </div>

    <!-- main navigation -->
      <nav id="topnav" role="navigation">
      <div class="menu-toggle">Menu</div>  
      <ul class="srt-menu" id="menu-main-navigation">
        <li class="current"><a href="?controller=pages&action=home">Home page</a></li>
        <li><a href="index.php?controller=posts&action=index">Posts</a></li>
        <li><a href="basic.html">Template Home page demo</a></li>
        <li><a href="basic-internal.html">Template Internal page demo</a></li>
        <li><a href="#">menu item 3</a>
        <ul>
          <li>
            <a href="#">menu item 3.1</a>
          </li>
          <li class="current">
            <a href="#">menu item 3.2</a>
            <ul>
              <li><a href="#">menu item 3.2.1</a></li>
              <li><a href="#">menu item 3.2.2 with longer link name</a></li>
              <li><a href="#">menu item 3.2.3</a></li>
              <li><a href="#">menu item 3.2.4</a></li>
              <li><a href="#">menu item 3.2.5</a></li>
            </ul>
            </li>
            <li><a href="#">menu item 3.3</a></li>
            <li><a href="#">menu item 3.4</a></li>
          </ul>
        </li>
        <li>
          <a href="#">menu item 4</a>
          <ul>
            <li><a href="#">menu item 4.1</a></li>
            <li><a href="#">menu item 4.2</a></li>
          </ul>
        </li>
        <li>
          <a href="#">menu item 5</a>
        </li>	
      </ul>     
    </nav><!-- end main navigation -->

  </header><!-- end header -->


  <section id="page-header" class="clearfix">    
    <!-- responsive FlexSlider image slideshow -->
    <div class="wrapper">
      <h1>PHP MVC sample with simple responsive template</h1>
    </div>
  </section>



  <!-- main content area -->   
  <div class="wrapper" id="main"> 

    <!-- content area -->    
    <section id="content">
    
      <?php require_once('routes.php'); ?>
    
    </section><!-- end content area -->   


    <!-- sidebar -->    
    <aside>
      <!--
      <h2>Secondary Section menu</h2>
      <nav id="secondary-navigation">
        <ul>
          <li><a href="#">menu item</a></li>
          <li class="current"><a href="#">current menu item</a></li>
          <li><a href="#">menu item</a></li>
          <li><a href="#">menu item</a></li>
          <li><a href="#">menu item</a></li>
        </ul>
      </nav>
      -->
    </aside><!-- #end sidebar -->

  </div><!-- #end div #main .wrapper -->


  <!-- footer area -->    
  <footer>
    <div id="colophon" class="wrapper clearfix">
      <!-- footer stuff -->
      &lt;<i>Copyright footer type stuff goes here</i>&gt;
    </div>

    <!--You can NOT remove this attribution statement from any page, unless you get the permission from prowebdesign.ro--><div id="attribution" class="wrapper clearfix" style="color:#666; font-size:11px;">Site built with <a href="http://www.prowebdesign.ro/simple-responsive-template/" target="_blank" title="Simple Responsive Template is a free software by www.prowebdesign.ro" style="color:#777;">Simple Responsive Template</a> by <a href="http://www.prowebdesign.ro/" target="_blank" title="www.prowebdesign.ro" style="color:#777;">Prowebdesign.ro</a></div><!--end attribution-->
  </footer><!-- #end footer area --> 


  <!-- jQuery -->
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="js/libs/jquery-1.9.0.min.js">\x3C/script>')</script>

  <script defer src="js/flexslider/jquery.flexslider-min.js"></script>

  <!-- fire ups - read this file!  -->   
  <script src="js/main.js"></script>

  </body>
</html>
