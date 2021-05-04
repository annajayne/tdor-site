<?php
    /**
     * Main HTML template.
     *
     */

    // router.php takes care of working out what we need to display based on the $controller and $action variables in the top level index.php file.
    require_once('views/router.php');


    /**
     *  Make a raw URL from the given controller and action.
     *
     *  @param string $controller           The controller.
     *  @param string $action               The action.
     *  @return string                      The generated URL.
     */
    function make_raw_url($controller, $action)
    {
         $url = '';

         if (ENABLE_FRIENDLY_URLS)
         {
             if ( ($controller === 'reports') && ($action === 'index') )
             {
                 $url = "/$controller";
             }
             else
             {
                 $url = "/$controller/$action";
             }
         }
         else
         {
             $url = "/index.php?controller=$controller&action=$action";
         }
         return $url;
    }


    /**
     * Get the HTML code for a link.
     *
     * @param string $url                   The URL for the link.
     * @param string $caption               The caption for the link.
     * @param string $rel                   The contents of the 'rel' attribute to be added. If blank, the attribute is omitted.
     * @return string                       The HTML code for the link.
     */
    function get_menuitem_html($url, $caption, $rel = '')
    {
        $menuitem = array('href' => $url, 'text' => $caption);

        if (!empty($rel) )
        {
            $menuitem['rel'] = $rel;
        }

        $html = get_link_html($menuitem);

        return $html;
    }


    $logged_in          = is_logged_in();
    $is_editor          = is_editor_user();
    $is_admin           = is_admin_user();

    // Should the "Blog" menu be shown?
    $show_blog          = $is_admin;

    if (!$is_admin)
    {
        $db             = new db_credentials();
        $blog_table     = new BlogTable($db);
        $blogpost_count = $blog_table->get_count(new BlogTableQueryParams);
        $show_blog      = ($blogpost_count >= 1);
    }

    $indent             = 4;
?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

<?php require_once('modules/header.php'); ?>
    <meta name="keywords" content="">

    <!-- Mobile viewport -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">

    <link rel="shortcut icon" href="/images/favicon.ico"  type="image/x-icon">


    <!-- CSS-->

    <!-- Google web fonts. You can get your own bundle at https://www.google.com/fonts. Don't forget to update the CSS accordingly!-->
    <link href='https://fonts.googleapis.com/css?family=Droid+Serif|Ubuntu' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/js/flexslider/flexslider.css">
    <link rel="stylesheet" href="/js/lightbox2/css/lightbox.min.css">
    <link rel="stylesheet" href="/css/basic-style.css">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/jquery-ui.min.css">
    <!--   <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" type="text/css">-->
    <link rel="stylesheet" href="/css/jquery.timepicker.min.css">
    <!-- end CSS-->


    <!-- JS-->

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/js/libs/jquery-1.9.0.min.js">\x3C/script>')</script>

    <!-- JQueryUI-->
    <script src="/js/libs/jquery-ui.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>-->

    <script src="/js/libs/jquery.timepicker.min.js"></script>

    <script src="/js/libs/jquery.cookie.js"></script>

    <script src="/js/libs/modernizr-2.6.2.min.js"></script>
    <script language="javascript" type="text/javascript" src="/js/sorttable/sorttable.min.js"></script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>

    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.css" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/MarkerCluster.Default.css" />
    <script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.4.1/leaflet.markercluster.js'></script>

    <script src="/js/misc.js"></script>
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
<?php
            echo '<li>'.get_menuitem_html('/',                                      'Home').'</li>';

            echo '<li>'.get_menuitem_html(make_raw_url('reports', 'index'),         'Reports');
            if ($is_editor || $is_admin)
            {
                echo   '<ul>';
                echo     '<li>'.get_menuitem_html('/reports?action=drafts',             'Drafts').'</li>';
                if ($is_editor)
                {
                    echo '<li>'.get_menuitem_html('/reports?action=add',                'Add').'</li>';
                }
                echo   '</ul>';
            }
            echo '</li>';

            echo '<li>'.get_menuitem_html(make_raw_url('pages', 'search'),          'Search').'</li>';
            echo '<li>'.get_menuitem_html(make_raw_url('pages', 'stats'),           'Statistics').'</li>';
            echo '<li>'.get_menuitem_html(make_raw_url('pages', 'api'),             'API').'</li>';

            if ($show_blog)
            {
                echo '<li>'.get_menuitem_html(make_raw_url('blog', ''),             'Blog').'</li>';
            }

            echo '<li>'.get_menuitem_html(make_raw_url('pages', 'about'),           'About').'</li>';
            echo '<li>'.get_menuitem_html(make_raw_url('pages', 'contact'),         'Contact', 'nofollow').'</li>';

            if ($is_admin)
            {
                $admin_url = make_raw_url('pages', 'admin');

                echo '<li>'.get_menuitem_html('#',                                  'Admin', 'nofollow');
                echo   '<ul>';
                echo     '<li>'.get_menuitem_html($admin_url.'?target=users',           'Administer Users', 'nofollow').'</li>';
                echo     '<li>'.get_menuitem_html($admin_url.'?target=blog',            'Administer Blog', 'nofollow').'</li>';

                echo     '<li>'.get_menuitem_html($admin_url.'?target=cleanup',         'Cleanup Data', 'nofollow').'</li>';

                echo     '<li>'.get_menuitem_html($admin_url.'?target=import',          'Import Reports', 'nofollow').'</li>';
                echo     '<li>'.get_menuitem_html($admin_url.'?target=rebuild',         'Rebuild Reports', 'nofollow').'</li>';
                echo     '<li>'.get_menuitem_html($admin_url.'?target=thumbnails',      'Build Report Thumbnails', 'nofollow').'</li>';
                echo     '<li>'.get_menuitem_html($admin_url.'?target=qrcodes',         'Build Report QR codes', 'nofollow').'</li>';
                echo     '<li>'.get_menuitem_html($admin_url.'?target=geocode',         'Geocode Reports', 'nofollow').'</li>';
                echo   '</ul>';
                echo '</li>';
            }

            if ($logged_in)
            {
                echo '<li>'.get_menuitem_html('/account',                           'Account', 'nofollow');
                echo   '<ul>';
                echo     '<li>'.get_menuitem_html('/account/logout',                    'Logout '.htmlspecialchars(get_logged_in_username() ), 'nofollow').'</li>';
                echo   '</ul>';
                echo '</li>';
            }
?>

        </ul> 
      </nav><!-- end main navigation (#topnav) -->
  
    </header><!-- end header -->
 
 
    <?php
        if ( ($controller === 'pages') && ($action === 'home') )
        {
            // Hero area (the grey one with a slider)
            require_once('modules/hero.php');
        }
        else
        {
            require_once('modules/banner.php');
        }
    ?>



    <!-- main content area -->   
    <div class="wrapper" id="main"> 

      <!-- content area -->    
      <section id="<?php echo get_content_style($controller, $action); ?>">
    
      <?php
        // route() works out what we need to display based on $controller and $action.
        route($controller, $action);
      ?>

      </section><!-- end content area -->   

      <!-- sidebar -->    
      <?php require_once('modules/sidebar.php'); ?>

    </div><!-- #end div #main .wrapper -->
    

    <!-- footer area -->    
    <footer class="nonprinting">

      <div id="colophon" class="wrapper clearfix">

        <!-- footer stuff -->
          <a href="https://twitter.com/tdorinfo" target="_blank">Twitter</a>&nbsp;&nbsp;
          <a href="https://www.facebook.com/groups/1570448163283501/" target="_blank">Facebook</a>&nbsp;&nbsp;
          <a href="mailto:tdor@translivesmatter.info">Email</a>
      </div>
      
      <!--You can NOT remove this attribution statement from any page, unless you get the permission from prowebdesign.ro--><div id="attribution" class="wrapper clearfix" style="color:#666; font-size:11px;">Site built with <a href="https://www.prowebdesign.ro/simple-responsive-template/" target="_blank"  rel="noopener" title="Simple Responsive Template is a free software by www.prowebdesign.ro" style="color:#777;">Simple Responsive Template</a> by <a href="http://www.prowebdesign.ro/" target="_blank" title="www.prowebdesign.ro" style="color:#777;">Prowebdesign.ro</a></div><!--end attribution-->

    </footer><!-- #end footer area --> 

    <div class="cw_popup">
        <div class="cw_inner">
          <h2>Trigger warning</h2>
          <p>This site contains reports of violence against transgender people, and links to detailed reports which contain graphic imagery.</p>
          <p>Please continue with caution.</p>
          <p><a class="cw_continue" href="#">Continue</a></p>
          <p><a class="cw_escape" href="<?php echo get_outa_here_url(); ?>">Get me out of here!</a></p>
        </div>
    </div>

    <script defer src="/js/flexslider/jquery.flexslider-min.js"></script>
    <script defer src="/js/lightbox2/js/lightbox.min.js"></script>

    <!-- fire ups - read this file!  -->   
    <script src="/js/main.js"></script>

  </body>
</html>
