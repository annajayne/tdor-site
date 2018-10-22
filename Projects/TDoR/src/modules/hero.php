<?php
    /**
     * Implementation of the hero area (slider etc.) on the main page.
     *
     */

    require_once('models/report.php');

    $recent_reports = Reports::get_most_recent(HOMEPAGE_SLIDER_ITEMS);

?>

    <section id="hero" class="clearfix">    
    <!-- responsive FlexSlider image slideshow -->
    <div class="wrapper">
       <div class="row"> 
        <div class="grid_5">

            <img src="/images/candle.jpg" height="58" width="65" style="float:left" /><h1 class="hero">Remembering Our Dead</h1><br />
            
            <div class="hero">

                <p>Every year on or around 20th November trans people worldwide gather for the <a href="https://tdor.info/about-2/" target="_blank">Transgender Day of Remembrance</a> to remember those we have lost to violence in the past year.</p>

                <p>This site gives details of trans people known to have been killed, as collated from reports by <a href="https://tdor.tgeu.org/" target="_blank">Transgender Europe</a> and trans activists worldwide.</p>

                <p>Details are also given for those lost to suicide where known.</p>

                <p><b>[TRIGGER WARNING: VIOLENCE. MURDER]</b></p>
            </div>

            <p>
              <?php
                $reports_url = ENABLE_FRIENDLY_URLS ? '/reports' : '/?category=reports&action=index';

                echo '<a href="'.$reports_url.'" class="buttonlink">Reports</a>';
              ?>
            </p>
        </div>

        <div class="grid_7 rightfloat">
              <div class="flexslider">
                  <ul class="slides">
                      <li>
                          <a href="/reports?view=list">
                              <img src="/images/tdor_candle_jars.jpg" alt="Every year on or around 20th November people worldwide gather for the Transgender day of Remembrance" />
                          </a>
                          <p class="flex-caption"><?php echo get_slider_main_caption(); ?>&nbsp;
                              <a href="https://twitter.com/search?q=%23tdor" target="_blank">#TDoR</a>&nbsp;
                              <a href="https://twitter.com/search?q=%23translivesmatter" target="_blank">#TransLivesMatter</a>
                          </p>
                      </li>
                      <li>
                          <a href="/reports?view=map">
                              <img src="/images/tdor_map.jpg" alt="Every year on or around 20th November people worldwide gather for the Transgender day of Remembrance" />
                          </a>
                          <p class="flex-caption">
                              <p class="flex-caption">
                                  <?php echo get_slider_main_caption(); ?>&nbsp;
                                  <a href="https://twitter.com/search?q=%23tdor" target="_blank">#TDoR</a>&nbsp;
                                  <a href="https://twitter.com/search?q=%23translivesmatter" target="_blank">#TransLivesMatter</a>
                              </p>
                      </li>
                      <?php
                        if (!empty($recent_reports) )
                        {
                            $default_image_pathname = get_photo_pathname('');               // Default flag image

                            foreach ($recent_reports as $report)
                            {
                                $url        = get_permalink($report);
                                $date       = get_display_date($report);
                                $cause      = get_displayed_cause_of_death($report);
                                $place      = $report->has_location() ? "$report->location ($report->country)" : $report->country;


                                $date       = str_replace(' ', '&nbsp;', $date);          // Replace spaces with non-breaking ones.

                                $caption    = "<b><a href='$url'>$report->name</a></b>";
                                $caption   .= ' '.$cause;
                                $caption   .= " in $place.";
                                $caption   .= ' <i>'.$date.'</i>';

                                $pathname = $default_image_pathname;

                                if ($report->photo_filename !== '')
                                {
                                    $pathname = "data/thumbnails/$report->photo_filename";
                                }

                                echo '<li>';
                                echo "<a href='$url'><img src='$pathname' /></a>";
                                echo "<p class='flex-caption'>$caption</p>";
                                echo '</li>';
                            }
                        }
                      ?>

                  </ul>
                </div><!-- FlexSlider -->
              </div><!-- end grid_7 -->
        </div><!-- end row -->
       </div><!-- end wrapper -->
    </section><!-- end hero area -->
