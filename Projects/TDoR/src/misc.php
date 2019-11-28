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
     * @return string               The current hostname. Hardcoded to 'https://tdor.translivesmatter.info' if this is a dev install or unit tests are running.
     */
    function get_host()
    {
        if (DEV_INSTALL || UNIT_TESTS)
        {
            $host = 'https://tdor.translivesmatter.info';
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
     * @return string               The current URL. Note that the hostname will be gardcoded to 'https://tdor.translivesmatter.info' if this is a dev install or unit tests are running.
     */
    function get_url()
    {
        return get_host().$_SERVER['REQUEST_URI'];
    }




    
/*
https://gist.github.com/phoenixg/5326222
*/
function HTTPStatus($num)
{
    $http = array(
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Bad Request',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
        505 => 'HTTP/1.1 505 HTTP Version Not Supported',
    );
 
    header($http[$num]);
 
    return
        array(
            'code' => $num,
            'error' => $http[$num],
        );
    }


    /**
     * Implementation function to send an email notification.
     *
     * @param string $from                  The source address.
     * @param string $to                    The destination address.
     * @param string $subject               The subject of the email.
     * @param string $content_html          The HTML text of the email to send, *without* <html> and <body> tags.
     */
    function send_email($from, $to, $subject, $content_html)
    {
        $headers = "From: $from\r\n";
        $headers .= "Reply-To: $from\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $message = "<html><body>$content_html</body></html>";

        if (!DEV_INSTALL)
        {
            mail($to, $subject, $message, $headers);
        }
    }


?>