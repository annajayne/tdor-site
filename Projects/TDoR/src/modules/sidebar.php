<?php
    /**
     *  Sidebar module.
     */

    $has_sidebar = true;
    if (($controller === 'pages') && (($action === 'admin') || ($action === 'search')))
    {
        $has_sidebar = false;
    }
    else if (($controller === 'reports') && (($action === 'index') || ($action === 'drafts') || ($action === 'recent')))
    {
        $has_sidebar = false;
    }

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