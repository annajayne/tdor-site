<?php
    /**
     * Predefined constants.
     *
     */

    define('DEV_INSTALL',                       file_exists('dev_install.ini') );
    define('UNIT_TESTS',                        file_exists('unittests.ini') );

    define('ENABLE_FRIENDLY_URLS',              true);
    define('HOMEPAGE_SLIDER_ITEMS',             15);
    define('ADMIN_EMAIL_ADDRESS',               'admin@translivesmatter.info');
    define('NOTIFY_EMAIL_ADDRESS',              'tdor@translivesmatter.info');

    define('DATE_FROM_COOKIE',                  'reports_date_from');
    define('DATE_TO_COOKIE',                    'reports_date_to');
    define('COUNTRY_COOKIE',                    'reports_country');
    define('VIEW_AS_COOKIE',                    'reports_view_as');
    define('FILTER_COOKIE',                     'reports_filter');

    define('CONTACT_SUBJECT_GENERAL',           'General enquiry');
    define('CONTACT_SUBJECT_MEDIA',             'Media enquiry');
    define('CONTACT_SUBJECT_RESOURCES',         'Resources/material for TDoR events');
    define('CONTACT_SUBJECT_REPORT_DETAILS',    'Additional details or a correction relating to an existing report');
    define('CONTACT_SUBJECT_NEW_REPORT',        'Details of someone who is not currently listed, but should be');
    define('CONTACT_SUBJECT_HELPING_OUT',       'Helping out with tdor.translivesmatter.info');
    define('CONTACT_SUBJECT_SOMETHING_ELSE',    'Something else');

?>