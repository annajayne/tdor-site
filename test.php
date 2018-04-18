<?php
    if (!function_exists('mysqli_init') && !extension_loaded('mysqli') )
    {
        echo 'mysqli is NOT available.<br>';
    }
    else
    {
        echo 'mysqli is available.<br>';
    }

    if (defined('PDO::ATTR_DRIVER_NAME') )
    {
        echo 'PDO is available.<br>';
    }
    else
    {
        echo 'PDO is NOT available.<br>';
    }

    phpinfo();
?>