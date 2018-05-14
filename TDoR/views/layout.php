<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>TDoR</title>
  <meta name="description" content="Remembering Our Dead">
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
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/jquery-ui.min.css">



  <!-- end CSS-->

  <!-- JS-->
    <!-- jQuery -->
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/libs/jquery-1.9.0.min.js">\x3C/script>')</script>


    <!-- JQueryUI-->
    <script src="js/libs/jquery-ui.min.js"></script>
   <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>-->



  <script src="js/libs/modernizr-2.6.2.min.js"></script>
  <script language="javascript" type="text/javascript" src="js/sorttable/sorttable.min.js"></script>
  <!-- end JS-->

</head>

<body id="home">

  <!-- header area -->
  <header class="wrapper clearfix">

    <div id="banner">        
      <!-- Add logo here if necessary -->
    </div>

    <!-- main navigation -->
      <nav id="topnav" role="navigation">
      <div class="menu-toggle">Menu</div>  
      <ul class="srt-menu" id="menu-main-navigation">
        <li><a href="index.php?controller=pages&action=home">Home</a></li>
        <li><a href="index.php?controller=posts&action=index">Reports</a></li>
        <li><a href="index.php?controller=pages&action=search">Search</a></li>
        <li><a href="index.php?controller=pages&action=about">About</a></li>
      </ul>
    </nav><!-- end main navigation (#topnav) -->

  </header><!-- end header -->


  <section id="page-header" class="clearfix">    
    <!-- responsive FlexSlider image slideshow -->
    <div class="wrapper">
      <img src="/images/candle.jpg" height="58" width="65" style="float:left" /><h1 style="color:#a00a0a;">Remembering Our Dead</h1>
    </div>
  </section>



  <!-- main content area -->   
  <div class="wrapper" id="main"> 

    <!-- content area -->    
    <section id="content">
    
      <!-- routes.php takes care of working out what we need to display based on the $controller and $action variables in the top level index.php file -->
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
        Site by <a href="https://about.me/annajayne" target="_blank">Anna-Jayne Metcalfe</a>
    </div>

    <!--You can NOT remove this attribution statement from any page, unless you get the permission from prowebdesign.ro--><div id="attribution" class="wrapper clearfix" style="color:#666; font-size:11px;">Site built with <a href="http://www.prowebdesign.ro/simple-responsive-template/" target="_blank" title="Simple Responsive Template is a free software by www.prowebdesign.ro" style="color:#777;">Simple Responsive Template</a> by <a href="http://www.prowebdesign.ro/" target="_blank" title="www.prowebdesign.ro" style="color:#777;">Prowebdesign.ro</a></div><!--end attribution-->
  </footer><!-- #end footer area --> 


  <script defer src="js/flexslider/jquery.flexslider-min.js"></script>

  <!-- fire ups - read this file!  -->   
  <script src="js/main.js"></script>

  </body>
</html>
