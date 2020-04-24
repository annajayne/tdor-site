<?php
    /**
     * Error page
     *
     */

    require_once('misc.php');


    $details = '';
    $url     = get_url();

    $status_code = http_response_code();

    if ( ($status_code == 200) && isset($_GET['code']) && isset($_GET['url']) )
    {
        $status_code    = $_GET['code'];

        $url            = urldecode($_GET['url']);
    }

    if ( ($status_code > 0) && ($status_code >= 400) )
    {
        $status = GetHttpStatus($status_code);

        $details = $status['error'];
    }

    echo '<h2>Error</h2>';
    echo '<div align="center">';
    echo   '<img src="/images/error.jpg" alt="It looks like something went wrong. Sorry!" />';
    echo '</div>';

    if (!empty($details) )
    {
        echo "<br>Details: <b>$details</b><br>URL: <span style='font-size: 0.8em;'>$url</span>";
    }

    echo '<p>&nbsp;</p>';
    echo "<p>This is the error page, so it looks like something went wrong. Our apologies.</p>";

?>