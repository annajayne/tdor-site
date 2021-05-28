<?php
    /**
     * Predefined constants.
     *
     */

    define('DEV_INSTALL',                       file_exists(__DIR__.'/dev_install.ini') );
    define('UNIT_TESTS',                        file_exists(__DIR__.'/unittests.ini') );

    define('CONFIG_FILE_PATH',                  '/config/tdor.ini');

    define('ENABLE_FRIENDLY_URLS',              true);
    define('HOMEPAGE_SLIDER_ITEMS',             15);
    define('SENDER_EMAIL_ADDRESS',              'noreply@translivesmatter.info');
    define('NOTIFY_EMAIL_ADDRESS',              'tdor@translivesmatter.info');

    define('DATE_FROM_COOKIE',                  'reports_date_from');
    define('DATE_TO_COOKIE',                    'reports_date_to');
    define('COUNTRY_COOKIE',                    'reports_country');
    define('CATEGORY_COOKIE',                   'reports_category');
    define('VIEW_AS_COOKIE',                    'reports_view_as');
    define('FILTER_COOKIE',                     'reports_filter');

    define('CONTACT_SUBJECT_GENERAL',           'General enquiry');
    define('CONTACT_SUBJECT_MEDIA',             'Media enquiry');
    define('CONTACT_SUBJECT_RESOURCES',         'Resources/material for TDoR events');
    define('CONTACT_SUBJECT_REPORT_DETAILS',    'Additional details or a correction relating to an existing report');
    define('CONTACT_SUBJECT_NEW_REPORT',        'Details of someone who is not currently listed, but should be');
    define('CONTACT_SUBJECT_HELPING_OUT',       'Helping out with tdor.translivesmatter.info');
    define('CONTACT_SUBJECT_SOMETHING_ELSE',    'Something else');

    define('BLOG_SUBTITLE_MAX_CHARS',           255);
    define('BLOG_SUBTITLE_MAX_WORDS',           40);

?>