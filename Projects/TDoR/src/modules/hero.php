<?php
    /**
     * Implementation of the hero area (slider etc.) on the main page.
     *
     */
    require_once('util/datetime_utils.php');                    // For date_str_to_display_date()
    require_once('models/reports.php');


    $db             = new db_credentials();
    $reports_table  = new Reports($db);

    $query_params = new ReportsQueryParams();

    $query_params->max_results      = HOMEPAGE_SLIDER_ITEMS;            // Limit the number of reports shown
    $query_params->sort_column      = 'date';                           // Sort by date, most recent first
    $query_params->sort_ascending   = false;                            //   "   "   "     "     "     "

    $recent_reports                 = $reports_table->get_all($query_params);

?>
    <img src="/images/translivesmatter.svg" class="translivesmatter_svg" />

    <section id="hero" class="clearfix">    
    <!-- responsive FlexSlider image slideshow -->
    <div class="wrapper">
       <div class="row"> 
        <div class="grid_5">

            <h1 class="hero">Remembering Our Dead</h1><br />
            
            <div class="hero">
                <p>Every year on or around 20th November trans people worldwide gather for the <a href="https://en.wikipedia.org/wiki/Transgender_Day_of_Remembrance" target="_blank" rel="noopener">Transgender Day of Remembrance</a> to remember those we have lost to violence in the past year.</p>

                <p>This site gives details of trans people known to have been killed, as collated from reports by <a href="https://tdor.tgeu.org/" target="_blank" rel="noopener">Transgender Europe</a> and trans activists worldwide.</p>

                <p>Details are also given for those lost to suicide where known.</p>

                <p><b>[TRIGGER WARNING: VIOLENCE. MURDER]</b></p>
            </div>

            <p>
              <?php
                $reports_url    = ENABLE_FRIENDLY_URLS ? '/reports' : '/?controller=reports&action=index';

                echo "<a href='$reports_url' class='button-dkred'>Reports</a>";

                // RSS feed. Note that the sizing and margins of the icon have been chosen to match the style of the "Reports" button alongside it.
                $rss_attributes = 'from=1901-01-01&to=2099-12-31&country=all&filter=&action=rss';
                $rss_feed_url   = ENABLE_FRIENDLY_URLS ? "$reports_url?$rss_attributes" : "$reports_url&$rss_attributes";
                $svg_attributes = "width='43' style='margin: 10px 15px 10px 0px;'";

                echo "<a href='$rss_feed_url' target='_blank'><img src='/images/rss.svg' alt='RSS' $svg_attributes /></a>";
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
                              <a href="https://twitter.com/search?q=%23tdor" target="_blank" rel="noopener">#TDoR</a>&nbsp;
                              <a href="https://twitter.com/search?q=%23translivesmatter" target="_blank" rel="noopener">#TransLivesMatter</a>
                          </p>
                      </li>
                      <li>
                          <a href="/reports?view=map">
                              <img src="/images/tdor_map.png" alt="Every year on or around 20th November people worldwide gather for the Transgender day of Remembrance" />
                          </a>
                          <p class="flex-caption">
                              <p class="flex-caption">
                                  <?php echo get_slider_main_caption(); ?>&nbsp;
                                  <a href="https://twitter.com/search?q=%23tdor" target="_blank" rel="noopener">#TDoR</a>&nbsp;
                                  <a href="https://twitter.com/search?q=%23translivesmatter" target="_blank" rel="noopener">#TransLivesMatter</a>
                              </p>
                      </li>
                      <?php
                        if (!empty($recent_reports) )
                        {
                            $default_image_pathname = get_photo_pathname('');               // Default flag image

                            foreach ($recent_reports as $report)
                            {
                                $url        = get_permalink($report);
                                $date       = date_str_to_display_date($report->date);
                                $cause      = get_displayed_cause_of_death($report);
                                $place      = get_displayed_location_with_country($report);

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
