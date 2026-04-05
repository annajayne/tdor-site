<?php
   /*
    *  Show reports to which the logged in user has contributed.
    */

    require_once('models/users.php');
    require_once('models/contributors.php');

    require_once('views/reports/reports_details_view_impl.php');

    echo "<h2>My Reports</h2>";

    $db = new db_credentials();

    $user_name          = get_logged_in_username();

    $users_table        = new Users($db);
    $user               = $users_table->get_user($user_name);

    $contributors_table = new Contributors($db);

    $reports            = $contributors_table->get_contributed_reports($user);

    // TODO(Contributors):
    //
    // - Add explanatory text, e.g.:
    //
    //      - Only draft reports are shown (maybe)
    //      - Reports must be reviewed by an edtor before being published etc.
    // - Hide publish links
    // - Add an option to view by thumbnails, i.e. show_thumbnails($reports)

    show_summary_table($reports);
?>
