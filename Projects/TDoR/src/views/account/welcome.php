<?php
    /**
     * Account homepage
     *
     */

    require_once('defines.php');
    require_once('utils.php');


    if (!is_logged_in() )
    {
        if (redirect_to('/account/login') )
        {
            exit;
        }
    }
    else
    {
        $username       = $_SESSION['username'];
        $api_key        = '';

        $is_api_user    = is_api_user();
        $is_editor      = is_editor_user();
        $is_admin       = is_admin_user();

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

        echo '<p>';
        echo   '<a href="/" class="button-blue">Homepage</a>&nbsp;';
        echo   '<a href="/reports" class="button-dkred">Reports</a>&nbsp;';

        if (!$is_editor)
        {
            $subject = CONTACT_SUBJECT_HELPING_OUT;

            $url = '/pages/contact?subject='.urlencode($subject);

            echo "<a href='$url' class='button-green' rel='nofollow'>Apply to become an editor</a>";
        }

        echo   '<a href="/account/change_password" class="button-blue">Change Password</a>&nbsp;';
        echo   '<a href="/account/logout" class="button-orange">Logout</a>';
        echo '</p>';
    }

?>
