<?php
    /**
     *  Sidebar module.
     */


    $has_sidebar = !( ($controller === 'pages') && ($action === 'admin') );
    
    if ($has_sidebar)
    {
        require_once('modules/tweets_sidebar.php');
    }
    
?>