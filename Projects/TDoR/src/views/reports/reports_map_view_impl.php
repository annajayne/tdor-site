<?php
    require_once('openstreetmap.php');


    function show_summary_map($reports)
    {
        if (!empty($reports) )
        {
            show_osm_map($reports);
        }
    }

?>