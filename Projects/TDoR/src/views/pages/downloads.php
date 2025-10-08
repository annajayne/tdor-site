<?php
    /**
     * Downloads page.
     *
     */

    function show_tdor_period_downloads($data)
    {
        $tdor_period                    = $data['tdor_period'];
        $preliminary_data                = isset($data['preliminary']) ? $data['preliminary'] : false;

        $reports_list_url               = $data['reports_list_url'];
        $reports_photos_url             = $data['reports_photos_url'];
        $reports_map_url                = $data['reports_map_url'];
        $reports_slides_url             = $data['reports_slides_url'];

        $reports_list_thumbnail_url     = '/downloads/tdor_list_thumbnail.png';

        $reports_slides_thumbnail_url   = isset($data['reports_slides_thumbnail_url']) ?
                                            $data['reports_slides_thumbnail_url'] :
                                            '/downloads/tdor_slides_thumbnail.png';

        $reports_photos_filename        = basename($reports_photos_url);
        $reports_map_filename           = basename($reports_map_url);
        $reports_slides_filename        = basename($reports_slides_url);

        $qualifier                      = $preliminary_data ? '[preliminary data]' : '';

        echo "<h3>TDoR $tdor_period $qualifier</h3>";
        echo "<div>"; // For the accordian

        echo   "<p>&nbsp;</p>";
        echo   "<div class='row'>";
        echo     "<div class='grid_3'>";
        echo       "<a href='$reports_list_url' target='_blank'><img src='$reports_list_thumbnail_url' /></a>";
        echo     "</div>";
        echo     "<div class='grid_9'>";
        echo       "<p>TDoR $tdor_period list of names [<a href='$reports_list_url' target='_blank'>View</a>]<br>(tip: print to PDF and download)</p>";
        echo     "</div>";
        echo   "</div>";

        if (!empty($reports_map_url))
        {
            echo   "<div class='row'>";
            echo     "<div class='grid_3'>";
            echo     "<a href='$reports_photos_url' rel='lightbox' title='Just some of the trans people we have lost during the TDoR $tdor_period period'><img src='$reports_photos_url' /></a>";
            echo     "</div>";
            echo     "<div class='grid_9'>";
            echo       "<p>TDoR $tdor_period victim photo collage [<a href='$reports_photos_url' download='$reports_photos_filename'>Download</a>]</p>";
            echo     "</div>";
            echo   "</div>";
        }

        if (!empty($reports_map_url))
        {
            echo   "<div class='row'>";
            echo     "<div class='grid_3'>";
            echo       "<a href='$reports_map_url' rel='lightbox' title='Map showing the locations where trans people are known to have passed away during the TDoR $tdor_period period'><img src='$reports_map_url' /></a>";
            echo     "</div>";
            echo     "<div class='grid_9'>";
            echo       "<p>TDoR $tdor_period map of cases [<a href='$reports_map_url' download='$reports_map_filename'>Download</a>]</p>";
            echo     "</div>";
            echo   "</div>";
        }

        if (!empty($reports_slides_url))
        {
            echo   "<div class='row'>";
            echo     "<div class='grid_3'>";
            echo       "<a href='$reports_slides_thumbnail_url' rel='lightbox' title='A preview of the first page of the slides. Feel free to <a href=\"$reports_slides_url\">download</a> and edit them as needed'><img src='$reports_slides_thumbnail_url' /></a>";
            echo     "</div>";
            echo     "<div class='grid_9'>";
            echo       "<p>TDoR $tdor_period Powerpoint slides [<a href='$reports_slides_url' download='$reports_slides_filename'>Download</a>]</p>";
            echo     "</div>";
            echo   "</div>";
        }

        echo "</div>"; // Closes the accordian
    }


    echo "<h2>Downloads and Resources</h2>";
    echo '<p>&nbsp;</p>';

    $tdor2024_data = array(
        'tdor_period' => 2024,
        'reports_list_url' => '/reports/tdor2024?country=all&filter=&sortup=1&view=list',
        'reports_photos_url' => '/downloads/tdor_2024_victims.png',
        'reports_map_url' => '/downloads/tdor_2024_map.png',
        'reports_slides_url' => '/downloads/tdor_2024_slides.pptx',
        'reports_slides_thumbnail_url' => '/downloads/tdor_2024_slides_thumbnail.png'
    );

    $tdor2023_data = array(
        'tdor_period' => 2023,
        'reports_list_url' => '/reports/tdor2023?country=all&filter=&sortup=1&view=list',
        'reports_photos_url' => '/downloads/tdor_2023_victims.png',
        'reports_map_url' => '/downloads/tdor_2023_map.png',
        'reports_slides_url' => '/downloads/tdor_2023_slides.pptx',
        'reports_slides_thumbnail_url' => '/downloads/tdor_2023_slides_thumbnail.png'
    );

    $tdor2022_data = array(
        'tdor_period' => 2022,
        'reports_list_url' => '/reports/tdor2022?country=all&filter=&sortup=1&view=list',
        'reports_photos_url' => '/downloads/tdor_2022_victims.png',
        'reports_map_url' => '/downloads/tdor_2022_map.png',
        'reports_slides_url' => '/downloads/tdor_2022_slides.pptx',
        'reports_slides_thumbnail_url' => '/downloads/tdor_2022_slides_thumbnail.png'
    );

    $tdor2021_data = array(
        'tdor_period' => 2021,
        'reports_list_url' => '/reports/tdor2021?country=all&filter=&sortup=1&view=list',
        'reports_photos_url' => '/downloads/tdor_2021_victims.png',
        'reports_map_url' => '/downloads/tdor_2021_map.png',
        'reports_slides_url' => '/downloads/tdor_2021_slides.pptx',
        'reports_slides_thumbnail_url' => '/downloads/tdor_2021_slides_thumbnail.png'
    );

    // We use the JQuery accordian widget here so that the downloads for specific TDoR periods
    // can be collapsed. The "active" property (see the bit of Javascript below) defines which
    //panel is initially active, if any.
    echo '<div id="accordion">';

    show_tdor_period_downloads($tdor2024_data);
    show_tdor_period_downloads($tdor2023_data);
    show_tdor_period_downloads($tdor2022_data);
    show_tdor_period_downloads($tdor2021_data);

    echo '</div"><br>';
?>

<script>
  $( function() {
      $("#accordion").accordion({
          heightStyle: "content",
          autoHeight: false,
          clearStyle: true,
          collapsible: true,
          active: 0
      });
  } );
</script>
