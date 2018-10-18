<?php

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

            $marker_text .= get_display_date($report).'<br>';
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
                echo '<div id="mapid" style="height: 600px;"></div>';
                echo '<script>';

                echo "var corner1 = L.latLng($lat_min, $lon_min);";
                echo "var corner2 = L.latLng($lat_max, $lon_max);";

                echo "bounds = L.latLngBounds(corner1, corner2);";
                echo "var mymap = L.map('mapid').setView([0, 0], 1);";
                echo "mymap.fitBounds(bounds);";
             //   echo "mymap.zoomOut();";

                echo "L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {";
		        echo        'maxZoom: 18,';
		        echo        "attribution: 'Map data &copy; <a href=\"https://www.openstreetmap.org/\">OpenStreetMap</a> contributors, ' +";
			    echo        "'<a href=\"https://creativecommons.org/licenses/by-sa/2.0/\">CC-BY-SA</a>, ' +";
			    echo        "'Imagery © <a href=\"https://www.mapbox.com/\">Mapbox</a>',";
                echo        "id: 'mapbox.streets'";
	            echo    '}).addTo(mymap);';


               echo "var markerClusters = L.markerClusterGroup();\n";

	           // echo 'L.marker([51.5, -0.09]).addTo(mymap).bindPopup("<b>Hello world!</b><br />I am a popup.").openPopup();';

                foreach ($reports_with_geo_data as $report)
                {
                    $varname = 'marker_'.$report->uid;

                    echo get_osm_marker_code($report, $varname);

                    echo "markerClusters.addLayer($varname);\n";
                }

                echo "mymap.addLayer(markerClusters);";

// echo 'var popup = L.popup();';

                //function onMapClick(e) {
                //    popup
                //        .setLatLng(e.latlng)
                //        .setContent("You clicked the map at " + e.latlng.toString())
                //        .openOn(mymap);
                //}

                //mymap.on('click', onMapClick);

                echo '</script>';
            }

            if (!empty($reports_without_geo_data) )
            {
                echo '<br><p>The following reports cannot be mapped as they do not contain location data:</p>';

                foreach ($reports_without_geo_data as $report)
                {
                    $permalink  = get_permalink($report);
                    $date       = get_display_date($report);
                    $place      = !empty($report->location) ? "$report->location, $report->country" : $report->country;

                    echo "<b><a href='$permalink'>$report->name</a></b> ($date in $place)<br>";
                }
            }

        }
    }

?>