<?php
    /**
     * Account homepage
     *
     */



    if (!is_logged_in() )
    {
        header("location: /account/login");
        exit;
    }
    
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
        $subject = 'tdor.translivesmatter.info editor application';
        $body    = 'Hi folks,%0A%0AI am interested in becoming an editor for tdor.translivesmatter.info.%0A%0A';
        $body   .= '<Please tell us a little bit about yourself here, including any language, research or programming etc. skills you think might be relevant>%0A%0A';
        $body   .= '%0ASincerely,%0A%0A<Your name here. Please remember to include any contact details/social media handles you think appropriate>%0A%0A%0A%0A';

        $url = 'mailto:tdor@translivesmatter.info?subject='.urlencode($subject).'&body='.urlencode($body);

        echo "<a href='$url' class='button-green'>Apply to become an editor</a>";
    }
    
    echo   '<a href="/account/change_password" class="button-blue">Change Password</a>&nbsp;';
    echo   '<a href="/account/logout" class="button-orange">Logout</a>';
    echo '</p>';

?>
