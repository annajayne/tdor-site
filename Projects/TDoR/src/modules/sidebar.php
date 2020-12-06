<?php
    /**
     *  Sidebar module.
     */
    require_once('lib/parsedown/Parsedown.php');                // https://github.com/erusev/parsedown
    require_once('lib/parsedown/ParsedownExtra.php');           // https://github.com/erusev/parsedown-extra
    require_once('lib/parsedown/ParsedownExtraPlugin.php');     // https://github.com/tovic/parsedown-extra-plugin#automatic-relnofollow-attribute-on-external-links


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