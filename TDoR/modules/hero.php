<?php

    function get_slider_main_caption()
    {
        $year       = date('Y');
        $month_day  = date('m-d');

        $verb       = ($month_day <= '11-20') ? 'is' : 'was';

        $caption    = "The Transgender Day of Remembrance $verb <b>20th November $year</b>";

        return $caption;
    }


    require_once('models/report.php');

    $recent_reports = Report::most_recent(HOMEPAGE_HERO_ITEMS);

?>

    <section id="hero" class="clearfix">    
    <!-- responsive FlexSlider image slideshow -->
    <div class="wrapper">
       <div class="row"> 
        <div class="grid_5">

            <img src="/images/candle.jpg" height="58" width="65" style="float:left" /><h1 class="hero">Remembering Our Dead</h1><br />
            
            <div class="hero">
                <p>Every year on or around 20th November people worldwide gather for the <a href="https://tdor.info/about-2/" target="_blank">Transgender Day of Remembrance</a> to remember trans people lost to violence in the past year.</p>

                <p>This site gives details of trans people known to have been killed worldwide, as collated from reports by <a href="https://tdor.tgeu.org/" target="_blank">Transgender Europe</a> and trans activists worldwide.</p>

                <p>Details are also given for those lost to suicide where known.</p>

                <p><b>[TRIGGER WARNING: VIOLENCE. MURDER]</b></p>
            </div>

            <p><a href="/?category=reports&action=index" class="buttonlink">Reports</a></p>
        </div>

        <div class="grid_7 rightfloat">
              <div class="flexslider">
                  <ul class="slides">
                      <li>
                          <a href="http://www.tdor.info">
                              <img src="/images/tdor_candle_jars.jpg" alt="Every year on or around 20th November people worldwide gather for the Transgender day of Remembrance" />
                          </a>
                          <p class="flex-caption"><?php echo get_slider_main_caption(); ?>&nbsp;
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
                                $url        = get_item_url($report);

                                $caption    = "<b><a href='$url'>$report->name</a></b>";
                                $caption   .= ' '.get_displayed_cause_of_death($report);
                                $caption   .= " in $report->location ($report->country).";
                                $caption   .= ' <i>'.get_display_date($report).'</i>';

                                $pathname = $default_image_pathname;

                                if ($report->photo_filename !== '')
                                {
                                    $pathname = "data/slider/$report->photo_filename";
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
