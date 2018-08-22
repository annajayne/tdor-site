<?php
    /**
     * Misc utility functions.
     *
     */


    /**
     * Log the given text. By default does nothing.
     *
     * @param string $text          The text to log.
     */
    function log_text($text)
    {
      // echo $text."<br>";
    }


    /**
     * Log the given error. By default just prints it.
     *
     * @param string $text          The error to log.
     */
    function log_error($text)
    {
        echo $text."<br>";
    }


    /**
     * Get the root filesystem folder, i.e. $_SERVER['DOCUMENT_ROOT']
     *
     * @return string               The the root filesystem folder.
     */
    function get_root_path()
    {
        return $_SERVER['DOCUMENT_ROOT'];
    }


    /**
     * Implementation function to get the current host name. Reserved for use by get_host().
     *
     * @return string               The current hostname (e.g. https://tdor.translivesmatter.info).
     */
    function raw_get_host()
    {
        return (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
    }


    /**
     * Get the current host name.
     *
     * @return string               The current hostname. Hardcoded to 'http://tdor.translivesmatter.info' if this is a dev install or unit tests are running.
     */
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


    /**
     * Get the current URL.
     *
     * @return string               The current URL. Note that the hostname will be gardcoded to 'http://tdor.translivesmatter.info' if this is a dev install or unit tests are running.
     */
    function get_url()
    {
        return get_host().$_SERVER['REQUEST_URI'];
    }


?>