<?php
    /**
     * Administrative command to geocode reports.
     *
     */


    /**
     * Geocode the given locations.
     *
     * @param array $locations              An array of locations.
     * @return array                        An array of geocoded locations.
     */
    function geocode_locations_impl($locations)
    {
        $geocoder_batch_limit   = 100;

        $chunks = array_chunk($locations, $geocoder_batch_limit, TRUE);

        $geocoded_places = array();

        foreach ($chunks as $chunk)
        {
            $batch_geocoded_places = geocode($chunk);

            foreach ($batch_geocoded_places as $geocoded_place)
            {
                $key = $geocoded_place['location'].'|'.$geocoded_place['country'];

                $geocoded_places[$key] = $geocoded_place;
            }
        }
        return $geocoded_places;
    }


    /**
     * Generate a unique key for the given location and country.
     *
     * @param string $location              The name of the location.
     * @param string $country               The name of the country.
     * @return string                       A key containing both location and country.
     */
    function get_geocode_location_key($location, $country)
    {
        return "$location|$country";
    }


   /**
     * Geocode reports.
     *
     */
     function geocode_locations()
    {
        require_once('models/reports.php');
        require_once('util/geocode.php');

        $db                 = new db_credentials();
        $reports_table      = new Reports($db);

        $reports            = $reports_table->get_all();

        $reports_to_geocode = array();
        $locations          = array();

        foreach ($reports as $report)
        {
            if (empty($report->latitude) || empty($report->longitude) )
            {
                $reports_to_geocode[] = $report;

                $key = get_geocode_location_key($report->location, $report->country);

                if (empty($locations[$key]) )
                {
                    $place = array();

                    $place['location']  = $report->location;
                    $place['country']   = $report->country;

                    $locations[$key]    = $place;
                }
            }
        }

        if (!empty($locations) )
        {
            $geocoded_places = geocode_locations_impl($locations);

            if (!empty($reports_to_geocode) )
            {
                foreach ($reports_to_geocode as $report)
                {
                    $key        = get_geocode_location_key($report->location, $report->country);

                    $permalink  = get_permalink($report);
                    $date       = get_display_date($report);
                    $place      = !empty($report->location) ? "$report->location, $report->country" : $report->country;

                    if (!empty($geocoded_places[$key]['lat']) )
                    {
                        $report->latitude   = $geocoded_places[$key]['lat'];
                        $report->longitude  = $geocoded_places[$key]['lon'];

                        echo "Geocoded <a href='$permalink'><b>$report->name</b></a> ($date / $place)<br>";

                        $reports_table->update($report);
                    }
                    else
                    {
                        echo "WARNING: Unable to geocode <a href='$permalink'><b>$report->name</b></a> ($date / $place)<br>";
                    }
                }
            }
        }

       echo 'Geocoding complete<br>';
    }

?>
