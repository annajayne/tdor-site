<?php
    require_once('util/utils.php');          // For get_config()
    require_once('util/datetime_utils.php'); // For date_str_to_display_date()


    function get_osm_marker_code($report, $varname)
    {
        if (!empty($report->latitude) && !empty($report->longitude) )
        {
            $name = htmlspecialchars($report->name, ENT_QUOTES, 'UTF-8');

            $photo_pathname = get_photo_pathname('');
            if (!empty($report->photo_filename) )
            {
                $photo_pathname = "/data/thumbnails/$report->photo_filename";
            }

            $marker_text = "<a href='$report->permalink'><b>$name</b></a><br>";
            if (!empty($report->age) )
            {
                $marker_text .= "Age $report->age<br>";
            }

            $marker_text .= date_str_to_display_date($report->date).'<br>';
            $marker_text .= htmlspecialchars( ($report->has_location() ? "$report->location ($report->country)" : $report->country), ENT_QUOTES, 'UTF-8');
            $marker_text .= '<br>';
            $marker_text .= ucfirst($report->cause).'<br>';

            $marker_text .= "<img src='$photo_pathname' /><br>";

            $marker = 'var '.$varname.' = L.marker(['.$report->latitude.', '.$report->longitude.']).addTo(mymap).bindPopup("'.$marker_text.'");';

            return $marker;
        }
    }


    function show_osm_map($reports)
    {
        if (!empty($reports) )
        {
            $reports_with_geo_data      = array();
            $reports_without_geo_data   = array();

            $lat_min = 90.0;
            $lat_max = -90.0;
            $lon_min = 180.0;
            $lon_max = -180.0;

            foreach ($reports as $report)
            {
                if (!empty($report->latitude) && !empty($report->longitude) )
                {
                    $reports_with_geo_data[] = $report;

                    // Expand the bounds. We use this to centre the map
                    if ($report->latitude < $lat_min)
                    {
                        $lat_min = $report->latitude;
                    }
                    if ($report->latitude > $lat_max)
                    {
                        $lat_max = $report->latitude;
                    }
                    if ($report->longitude < $lon_min)
                    {
                        $lon_min = $report->longitude;
                    }
                    if ($report->longitude > $lon_max)
                    {
                        $lon_max = $report->longitude;
                    }
                }
                else
                {
                    $reports_without_geo_data[] = $report;
                }
            }


            if (!empty($reports_with_geo_data) )
            {
                $site_config = get_config();
                $api_key     = $site_config['MapBox']['api_key'];

                echo "\n<div id='mapid' style='height: 600px;'></div>\n";
                echo '<script>'.PHP_EOL;

                echo "var corner1 = L.latLng($lat_min, $lon_min);\n";
                echo "var corner2 = L.latLng($lat_max, $lon_max);\n";

                echo "bounds = L.latLngBounds(corner1, corner2);\n";
                echo "var mymap = L.map('mapid').setView([0, 0], 1);\n";
                echo "mymap.fitBounds(bounds);\n";

                echo "L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {\n";
                echo        "tileSize: 512,\n";
                echo        "maxZoom: 18,\n";
                echo        "zoomOffset: -1,\n";
                echo        "attribution: 'Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> &amp; contributors; Imagery &copy; <a href=\"https://www.mapbox.com/\">Mapbox</a>',\n";
                echo        "id: 'mapbox/streets-v11',\n";
                echo        "accessToken: '$api_key'\n";
                echo    '}).addTo(mymap);'.PHP_EOL;


                echo "var markerClusters = L.markerClusterGroup();\n";

                foreach ($reports_with_geo_data as $report)
                {
                    $varname = 'marker_'.$report->uid;

                    echo get_osm_marker_code($report, $varname);

                    echo "markerClusters.addLayer($varname);\n";
                }

                echo "mymap.addLayer(markerClusters);\n";

                // If there is only a single report, zoom out to level 6 ("large European country")
                // ref https://wiki.openstreetmap.org/wiki/Zoom_levels
                if (count($reports_with_geo_data) == 1)
                {
                    echo "mymap.setZoom(6);\n";
                }

                echo "</script>\n";
            }

            if (!empty($reports_without_geo_data) )
            {
                echo '<br><p>The following reports cannot be mapped as they do not contain location data:</p>';

                foreach ($reports_without_geo_data as $report)
                {
                    $permalink  = get_permalink($report);
                    $date       = date_str_to_display_date($report->date);
                    $place      = !empty($report->location) ? "$report->location, $report->country" : $report->country;

                    echo "<b><a href='$permalink'>$report->name</a></b> ($date in $place)<br>";
                }
            }

        }
    }

?>