<?php
    /**
     * Account homepage
     *
     */

    require_once('defines.php');
    require_once('util/utils.php');

    $redirect_url = '';

    if (isset($_GET['url']) )
    {
        $redirect_url = urldecode($_GET['url']);
    }

    if (!is_logged_in() )
    {
        $url = '/account/login';

        if (!empty($redirect_url) )
        {
            $url = $url.'?url='.urlencode($redirect_url);
        }

        if (redirect_to($url) )
        {
            exit;
        }
    }
    else
    {
        if (!empty($redirect_url) && redirect_to($redirect_url))
        {
            exit;
        }

        $site_config        = get_config();

        $edits_disabled     = (bool)$site_config['Admin']['edits_disabled'];
        $edits_disabled_msg = $site_config['Admin']['edits_disabled_message'];

        $username           = $_SESSION['username'];
        $email              = $_SESSION['email'];
        $api_key            = '';

        $is_api_user        = is_api_user();
        $is_editor          = is_editor_user();
        $is_admin           = is_admin_user();

        if ($is_api_user && isset($_SESSION['api_key']) )
        {
            $api_key = $_SESSION['api_key'];
        }

        $roles = '';
        if ($is_api_user)
        {
            $roles = 'API user; ';
        }

        if ($is_editor)
        {
            $roles .= 'Editor; ';
        }

        if ($is_admin)
        {
            $roles .= 'Admin; ';
        }

        $roles = rtrim($roles, '; ');


        ////////////////////////////////////////////////////////////////////////////////
        // Form content

        echo '<h1>Welcome, <b>'.htmlspecialchars($username).'</b> ('.$roles.') </h1>';
        echo '<p>&nbsp;</p>';

        if ($is_api_user)
        {
            echo "<h3>API key: $api_key</h3>";
        }
        echo '<p>&nbsp;</p>';

        if ($edits_disabled && ($is_editor || $is_admin) )
        {
            echo "<p><span class='account-error'><b>$edits_disabled_msg</b></span></p>";
        }

        echo '<p>';
        echo   '<a href="/" class="button-blue">Homepage</a>&nbsp;';
        echo   '<a href="/reports" class="button-dkred">Reports</a>&nbsp;';

        if (!$is_editor)
        {
            $subject = CONTACT_SUBJECT_HELPING_OUT;
            $params   = '?name='.urlencode($username);
            $params  .= '&email='.urlencode($email);
            $params  .= '&subject='.urlencode($subject);

            $url = "/pages/contact?$params";

            echo "<a href='$url' class='button-green' rel='nofollow'>Apply to become an editor</a>";
        }

        echo   '<a href="/account/change_password" class="button-blue">Change Password</a>&nbsp;';
        echo   '<a href="/account/logout" class="button-orange">Logout</a>';
        echo '</p>';
    }
?>
