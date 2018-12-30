<?php
    /**
     * Administrative commands to rebuild the database etc.
     *
     */

    require_once('views/pages/admin/database_rebuild.php');
    require_once('views/pages/admin/report_geocoding.php');
    require_once('views/pages/admin/report_thumbnails.php');
    require_once('views/pages/admin/report_qrcodes.php');



    $target = $_GET['target'];

    switch ($target)
    {
        case 'thumbnails':
            rebuild_thumbnails();
            break;

        case 'qrcodes':
            rebuild_qrcodes();
            break;

        case 'geocode':
            geocode_locations();
            break;

        default:
            rebuild_database();
            break;
    }

?>
