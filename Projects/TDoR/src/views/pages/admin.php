<?php
    /**
     * Administrative commands to rebuild the database etc.
     *
     */

    require_once('views/pages/admin/show_users.php');
    require_once('views/pages/admin/database_rebuild.php');
    require_once('views/pages/admin/report_geocoding.php');
    require_once('views/pages/admin/report_thumbnails.php');
    require_once('views/pages/admin/report_qrcodes.php');
    require_once('views/pages/admin/data_cleanup.php');



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

        case 'cleanup':
           data_cleanup();
           break;

        case 'users':
           show_users();
           break;

        default:
            rebuild_database();
            break;
    }

?>
