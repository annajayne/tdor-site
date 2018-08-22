<?php
    /**
     * Predefined constants.
     * 
     */

    define('DEV_INSTALL',           file_exists('dev_install.ini') );
    define('UNIT_TESTS',            file_exists('unittests.ini') );

    define('ENABLE_FRIENDLY_URLS',  true);
    define('HOMEPAGE_SLIDER_ITEMS', 15);

    define('DATE_FROM_COOKIE',      'reports_date_from');
    define('DATE_TO_COOKIE',        'reports_date_to');
    define('VIEW_AS_COOKIE',        'reports_view_as');
    define('FILTER_COOKIE',         'reports_filter');

?>