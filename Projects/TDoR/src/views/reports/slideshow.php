<?php
    /**
     * "Reports" slideshow page.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_display_date()
    require_once('models/reports.php');
    require_once('controllers/reports_controller.php');


    // Retrieve data on the report(s) to show as a slideshow.
    $controller         = new ReportsController();

    $params             = $controller->get_current_params();
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
    <style>
    p
    {
      font-size: 2em;
      line-height: 2.0em;
    }

    /* Container holding the image and the text */
    .container
    {
        position: relative;
        text-align: center;
        color: white;
    }

    /* QRCode */
    .qrcode_overlay
    {
        position: absolute;
        bottom:16px;
        right: 16px;
    }
    </style>

    <!-- Google web fonts. You can get your own bundle at https://www.google.com/fonts. Don't forget to update the CSS accordingly!-->
    <link href='https://fonts.googleapis.com/css?family=Droid+Serif|Ubuntu' rel='stylesheet' type='text/css'>

    <link rel="stylesheet" href="/css/normalize.css">
    <link rel="stylesheet" href="/js/flexslider/flexslider.css">
    <link rel="stylesheet" href="/css/basic-style.css">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/jquery-ui.min.css">
    <!-- end CSS-->


    <!-- JS-->

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/js/libs/jquery-1.9.0.min.js">\x3C/script>')</script>

    <!-- JQueryUI-->
    <script src="/js/libs/jquery-ui.min.js"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>-->

    <script src="/js/libs/modernizr-2.6.2.min.js"></script>

    <!-- end JS-->
  </head>


  <body id="home" style="background-color:#0A0A0A;">
    <!-- responsive FlexSlider image slideshow -->
    <div class="wrapper">

      <div class="flexslider">
        <ul class="slides">
          <li>
            <a href="http://www.tdor.info">
              <img src="/images/tdor_candle_jars.jpg" alt="Every year on or around 20th November people worldwide gather for the Transgender day of Remembrance" />
            </a>
            <p class="flex-caption">
              <?php echo get_slider_main_caption(); ?>&nbsp;
              <a href="https://twitter.com/search?q=%23tdor" target="_blank" rel="noopener">#TDoR</a>&nbsp;
              <a href="https://twitter.com/search?q=%23translivesmatter" target="_blank" rel="noopener">#TransLivesMatter</a>
            </p>
          </li>

          <?php
            if (!empty($params->reports) )
            {
                $default_image_pathname = get_photo_pathname('');               // Default flag image

                foreach ($params->reports as $report)
                {
                    $url        = get_permalink($report);
                    $date       = date_str_to_display_date($report->date);
                    $cause      = get_displayed_cause_of_death($report);
                    $place      = get_displayed_location_with_country($report);

                    $date = str_replace(' ', '&nbsp;', $date);          // Replace spaces with non-breaking ones.

                    $caption    = "<b><a href='$url'>$report->name</a></b>";
                    $caption   .= ' '.$cause;
                    $caption   .= " in $place.";
                    $caption   .= ' <i>'.$date.'</i>';

                    $pathname = $default_image_pathname;

                    if ($report->photo_filename !== '')
                    {
                        $pathname = "data/thumbnails/$report->photo_filename";
                    }

                    $qrcode_pathname = "data/qrcodes/$report->uid.png";

                    echo '<li>';
                    echo   '<div class="container">';
                    echo     "<a href='$url'><img src='$pathname' /></a>";
                    echo     '<div class="qrcode_overlay">';
                    echo       "<img src='$qrcode_pathname' />";
                    echo     '</div>';
                    echo   '</div>';
                    echo   "<p class='flex-caption'>$caption</p>";
                    echo '</li>';
                }
            }
          ?>

        </ul>
      </div><!-- FlexSlider -->
    </div><!-- end wrapper -->


    <script defer src="/js/flexslider/jquery.flexslider-min.js"></script>

    <script>
      $(document).ready(function()
      {
          // initialise  slideshow
          $('.flexslider').flexslider(
          {
              animation: "slide",
              controlNav: false,
              start: function(slider)
              {
                  $('body').removeClass('loading');
              }
          });
      });
    </script>

  </body>
</html>

