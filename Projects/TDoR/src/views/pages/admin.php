<?php
    /**
     * Administrative commands to rebuild the database etc.
     *
     */

    require_once('views/pages/admin/show_users.php');
    require_once('views/pages/admin/import_reports.php');
    require_once('views/pages/admin/database_rebuild.php');
    require_once('views/pages/admin/report_geocoding.php');
    require_once('views/pages/admin/report_thumbnails.php');
    require_once('views/pages/admin/report_qrcodes.php');
    require_once('views/pages/admin/report_issues.php');
    require_once('views/pages/admin/data_cleanup.php');
    require_once('views/pages/admin/administer_blog.php');



    $target = $_GET['target'];

    switch ($target)
    {
        case 'issues':
            display_report_issues();
            break;

        case 'cleanup':
           data_cleanup();
           break;

        case 'import':
            import_reports();
            break;

        case 'geocode':
            geocode_locations();
            break;

        case 'qrcodes':
            rebuild_qrcodes();
            break;

        case 'rebuild':
            rebuild_database();
            break;

        case 'thumbnails':
            rebuild_thumbnails();
            break;

        case 'users':
            show_users();
            break;

        case 'blog':
            administer_blog();
            break;

        default:
            break;
    }

?>
