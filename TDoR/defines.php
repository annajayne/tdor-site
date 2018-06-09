<?php
    define('DEV_INSTALL',           file_exists('dev_install.ini') );

    define('ENABLE_FRIENDLY_URLS',  false);
    define('HOMEPAGE_SLIDER_ITEMS', 15);

    define('ALLOW_REPORT_EDITING',  DEV_INSTALL ? true : false);
    define('SHOW_REBUILD_MENUITEM', DEV_INSTALL ? true : false);
?>