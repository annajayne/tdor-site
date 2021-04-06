<?php
    /**
     *  Sidebar module.
     */


    $has_sidebar = !( ($controller === 'pages') && ($action === 'admin') );
    
    if ($has_sidebar)
    {
        if ( ($controller === 'reports') && ( ($action === 'add') || ($action === 'edit') ) )
        {
            require_once('modules/report_editing_sidebar.php');
        }
        else
        {
            require_once('modules/tweets_sidebar.php');
        }
    }
    
?>