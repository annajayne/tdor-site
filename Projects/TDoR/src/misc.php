<?php

    function log_text($text)
    {
      //  echo $text."<br>";
    }


    function log_error($text)
    {
        echo $text."<br>";
    }


    function raw_get_host()
    {
        return (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
    }


    function get_host()
    {
        if (DEV_INSTALL || UNIT_TESTS)
        {
            $host = 'http://tdor.translivesmatter.info';
        }
        else
        {
            $host = raw_get_host();
        }
        return $host;
    }


    function get_url()
    {
        return get_host().$_SERVER['REQUEST_URI'];
    }


?>