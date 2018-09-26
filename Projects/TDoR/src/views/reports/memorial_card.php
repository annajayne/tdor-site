<?php

    /**
     * Get the displayed cause of death (as used in memorial cards) corresponding to the given report.
     * 
     * Aside from capitalising the first character the only cause this function adjusts is "not reported", which is changed to "killed".
     *
     * @param Report $report                      The source report.
     * @return string                             The corresponding cause of death.
     */
    function get_cause_of_death_for_memorial_card($report)
    {
        $cause = $report->cause;

        if (stripos($report->cause, 'not reported') !== false)
        {
            $cause = "killed";
        }
        return ucfirst($cause);
    }


    require_once('models/report.php');
    require_once('controllers/reports_controller.php');


    // Retrieve data on the report(s) to export.
    $controller         = new ReportsController();
    $params             = $controller->get_current_params();
?>


<html>
  <head>
    <meta charset="utf-8" />
    <style>
        @media print
        {
            @page
            {
                margin-top: 0;
                margin-bottom: 0;
            }

            hr
            {
                background-color: white;
                height: 1px;
                border: 0;
                page-break-after: always;
            }
        }

        p.name
        {
            font-weight: bold;
            font-size: 48pt;
            margin-top: 0.5em;
            margin-bottom: 0;
            padding: 0;
        }  

      p.age
      {
        font-weight: bold;
        font-size: 32pt;
        line-height: 0.25;
      }

      p.location
      {
        font-weight: bold;
        font-size: 28pt;
        line-height: 0.5;
      }  

      p.cause
      {
        font-weight: bold;
        font-size: 24pt;
      }  

      p.description
      {
        font-size: 24pt;
      }  

      p.permalink
      {
        font-size: 12pt;
      }

      /* QRCode */
      .qrcode_overlay
      {
          position: block;
          bottom: 16px;
          right: 16px;
      }
    </style>
  </head>

  <body>

      <?php
        $candle_placeholder = '/images/memorial_card_candle_placeholder.png';

        foreach ($params->reports as $report)
        {
            $date               = date_str_to_display_date($report->date);
            $cause              = get_cause_of_death_for_memorial_card($report);
            $place              = $report->has_location() ? "$report->location ($report->country)" : $report->country;
            $photo_pathname     = get_photo_pathname('');
            $qrcode_pathname    = "/data/qrcodes/$report->uid.png";
            $short_description  = get_short_description($report);
            $permalink          = get_host().get_permalink($report);

            if (!empty($report->photo_filename) )
            {
                $photo_pathname = "/data/thumbnails/$report->photo_filename";
            }

            echo '<div style="width:800px;" align="center"><br><br><br>';

            echo   "<img src='$candle_placeholder' />";

            echo   "<p class='name'>$report->name</p>";

            if (!empty($report->age) )
            {
                echo "<p class='age'>Age $report->age</p>";
            }
            else
            {
                echo "<p class='age'>&nbsp;</p>";       // Placeholder
            }

            echo   "<p class='location'>$place</p>";
            echo   "<p class='cause'>$cause on $date</p>";

            echo   '<div style="position:relative;">';
            echo     "<img src='$photo_pathname' width='800' height='400' />";
            echo     "<img src='$qrcode_pathname' width='82' height='82' style='position:absolute; bottom:10px; right:10px;' />";
            echo   '</div>';

            echo   "<p class='description'>$short_description</p>";

            echo   '<p class="permalink">';
            echo     "<a href='$permalink'>$permalink</a>";
            echo   '</p>';
            echo '</div>';
            echo '<hr>';
        }
      ?>

  </body>
</html>