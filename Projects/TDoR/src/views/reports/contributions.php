<?php
   /*
    *  Show reports to which the logged in user has contributed.
    */

    require_once('models/users.php');
    require_once('models/contributors.php');

    require_once('views/reports/reports_details_view_impl.php');

    echo "<h2>My Reports</h2>";
    echo '<p>This page lists the reports you have submitted.</p>';

    $db = new db_credentials();

    $user_name          = get_logged_in_username();

    $users_table        = new Users($db);
    $user               = $users_table->get_user($user_name);

    $contributors_table = new Contributors($db);

    $reports            = $contributors_table->get_contributed_reports($user);

    // TODO(Contributors): Add an option to view by thumbnails, i.e. show_thumbnails($reports)

    show_summary_table($reports);

    echo '<p>&nbsp;</p>';
    echo '<div class="info grid_8" align="left">';
    echo   '<p>You can edit the contents of any of your reports as long as they remain as drafts, but once published a report will be read only.</p>';
    echo   '<p>Any reports you add will not be published until verified and reviewed - in order to do this an editor may need to contact you to request additional information.</p>';
    echo '</div>';
?>
