<?php

    function log_text($text)
    {
      //  echo $text."<br>";
    }


    function log_error($text)
    {
        echo $text."<br>";
    }


    function get_host()
    {
        $host = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];

        if (DEV_INSTALL)
        {
            $host = 'http://tdor.annasplace.me.uk';
        }
        return $host;
    }


    function get_url()
    {
        return get_host().$_SERVER['REQUEST_URI'];
    }


?>