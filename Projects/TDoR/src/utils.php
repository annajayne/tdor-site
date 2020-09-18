<?php
    /**
     * General utility functions.
     *
     */
    require_once('lib/tuupola/base62/src/Base62/BaseEncoder.php');
    require_once('lib/tuupola/base62/src/Base62/PhpEncoder.php');
    require_once('lib/tuupola/base62/src/Base62.php');
    require_once('lib/random_bytes/random.php');                            // random_bytes() implementation in case we're running on < PHP 7.0
    require_once('defines.php');                                            // For CONFIG_FILE_PATH
    require_once('misc.php');                                               // For get_root_path()



    /**
     * Determine if the string $haystack starts with $needle.
     *
     * @param string $haystack      The string to search in
     * @param string $needle        The string to search for.
     * @return boolean              Returns true if $haystack starts with $needle; false otherwise.
     */
    function str_begins_with($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }


    /**
     * Determine if the string $haystack ends with $needle.
     *
     * @param string $haystack      The string to search in
     * @param string $needle        The string to search for.
     * @return boolean              Returns true if $haystack ends with $needle; false otherwise.
     */
    function str_ends_with($haystack, $needle)
    {
        return strrpos($haystack, $needle) + strlen($needle) === strlen($haystack);
    }


    /**
     * Determine if the given string represents a valid hex value.
     *
     * @param string $value         The string to check.
     * @return boolean              Returns true if $value is a valid hex value; false otherwise.
     */
    function is_valid_hex_string($value)
    {
        return (dechex(hexdec($value) ) === ltrim($value, '0') );
    }


    /**
     * Return a random hex string of the specified length.
     *
     * @param int $num_bytes        The length in bytes of the generated value.
     * @return string               The generated hex value.
     */
    function get_random_hex_string($num_bytes = 4)
    {
        return bin2hex(openssl_random_pseudo_bytes($num_bytes) );
    }


    /**
     * Get the cookie with the specified name.
     *
     * @param string $name          The name of the cookie.
     * @param string $default_value The default value of the cookie.
     * @return string               The value of the cookie, or $default_value if not set.
     */
    function get_cookie($name, $default_value)
    {
        if (isset($_COOKIE[$name]) )
        {
            return $_COOKIE[$name];
        }
        return $default_value;
    }


    /**
     * Set the value of the specified cookie.
     *
     * @param string $name          The name of the cookie.
     * @param string $value         The value of the cookie.
     * @return void
     */
    function set_cookie($name, $value)
    {
        echo "<script>set_session_cookie('$name', '$value');</script>";
    }


    /**
     * https://stackoverflow.com/questions/7409512/new-line-to-paragraph-function/7409591#7409591
     *
     * @param string $text          A string containing the text.
     * @param boolean $line_breaks  true if line breaks are desired; false otherwise.
     * @param boolean $xml          true for XML output; false otherwise.
     * @return string               The corresponding HTML.
     */
    function nl2p($text, $line_breaks = true, $xml = true)
    {
        $text = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $text);

        // It is conceivable that people might still want single line-breaks
        // without breaking into a new paragraph.
        if ($line_breaks == true)
        {
            return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'), trim($text)).'</p>';
        }
        else
        {
            return '<p>'.preg_replace(
            array("/([\n]{2,})/i", "/([\r\n]{3,})/i","/([^>])\n([^<])/i"),
            array("</p>\n<p>", "</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'),

            trim($text) ).'</p>';
        }
    }


    /**
     * Convert text to html
     *
     * @param string $text          A string containing the text.
     * @return string               The corresponding HTML.
     */
    function nl2p2($text)
    {
        $paragraphs = '';

        foreach (explode("\n", $text) as $line)
        {
            if (trim($line) )
            {
                $paragraphs .= '<p>' . $line . '</p>';
            }
        }

        return $paragraphs;
    }


    /**
     * Get the HTML for a link.
     *
     * @param array $link_properties  An array giving the properties of the link.
     * @return string                 The generated HTML code.
     */
    function get_link_html($link_properties)
    {
        $html = '<a ';

        if (isset($link_properties['onclick']) )
        {
            $html .= 'onclick="'.$link_properties['onclick'].'" ';
        }

        if (isset($link_properties['rel']) )
        {
            $html .= 'rel="'.$link_properties['rel'].'" ';
        }

        if (isset($link_properties['target']) )
        {
            $html .= 'target="'.$link_properties['target'].'" ';
        }

        $html .= 'href="'.$link_properties['href'].'">'.$link_properties['text'].'</a>';

        return $html;
    }


    /**
     * Mash-up version of nl2p2() which also turns basic markdown into HTML for display.
     *
     * The following markdown constructs are handled:
     *
     *      > <text>    - blockquote.
     *      - <text>    - unordered list.
     *
     * @param string $markdown      A string containing the markdown text.
     * @return string               The corresponding HTML.
     */
    function markdown_to_html($markdown)
    {
        $html = '';

        $blockquote = false;
        $unordered_list = false;

        foreach (explode("\n", $markdown) as $line)
        {
            if (trim($line) )
            {
                $blockquote_markup = '> ';
                $unordered_list_markup = '- ';

                if (strpos($line, $blockquote_markup) === 0)
                {
                    $line = substr($line, strlen($blockquote_markup) );

                    if (!$blockquote)
                    {
                        $blockquote = true;
                        $html .= '<blockquote>';
                    }
                }
                else if ($blockquote)
                {
                    $html .= '</blockquote>';
                    $blockquote = false;
                }

                if (strpos($line, $unordered_list_markup) === 0)
                {
                    $line = substr($line, strlen($unordered_list_markup) );

                    if (!$unordered_list)
                    {
                        $unordered_list = true;
                        $html .= '<ul>';
                    }

                    $html .= "<li>$line<br>&nbsp;</li>";
                    continue;
                }
                else if ($unordered_list)
                {
                    $html .= '</ul>';
                    $unordered_list = false;
                }

                $html .= '<p>' . $line . '</p>';
            }
        }

        // NB this could cause closing quotes to be added in the wrong order.
        // We should be able to get round this using RAII objects [Anna 19.5.2018].
        if ($blockquote)
        {
            $html .= '</blockquote>';
        }
        if ($unordered_list)
        {
            $html .= '</ul>';
        }
        return $html;
    }



    /**
     * Turn all URLs in clickable links.
     *
     * https://stackoverflow.com/questions/7409512/new-line-to-paragraph-function/7409591#7409591
     *
     * https://gist.github.com/jasny/2000705
     *
     * @param string $value
     * @param array  $protocols  http/https, ftp, mail, twitter
     * @param array  $attributes
     * @return string
     */
    function linkify($value, $protocols = array('http', 'mail'), array $attributes = array() )
    {
        // Link attributes
        $attr = '';
        foreach ($attributes as $key => $val)
        {
            $attr = ' ' . $key . '="' . htmlentities($val) . '"';
        }

        $links = array();

        // Extract existing links and tags
        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);

        // Extract text links for each protocol
        foreach ( (array)$protocols as $protocol)
        {
            switch ($protocol)
            {
                case 'http':
                case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:)])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$protocol://$link</a>") . '>'; }, $value); break;
                case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
                case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>'; }, $value); break;
                default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
            }
        }

        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }


   /**
     * Get the user agent
     *
     * @return string               The user agent.
     */
    function get_user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }


   /**
     * Determine whether the given user agent string is a bot.
     *
     * @return boolean              true if the given agent appears to be a bot; false otherwise.
     */
    function is_bot($agent)
    {
        return preg_match('/bot|crawl|slurp|spider|mediapartners/i', $agent) ? true : false;
    }

    /**
     * Redirect to the specified URL.
     *
     * If headers have already been sent, a Javascript redirect is used.
     *
     * @param string $url           The URL to redirect to
     * @return boolean              True if a redirect without headers was sent (in this case the call should be followed immediately by exit; )
     */
    function redirect_to($url, $status_code = 0)
    {
        if (headers_sent() )
        {
            echo "<script>window.location.replace('$url');</script>";
        }
        else
        {
            if ($status_code > 0)
            {
                $status = GetHttpStatus($status_code);

                header($status['error'], TRUE, $status_code);
            }

            header("location: $url");

            return true;
        }
        return false;
    }


    /**
     * Read the site config from the specified ini file.
     *
     * @param string $pathname      The pathanme of the ini file.
     * @return array                An array containing the contents of the ini file.
     */
    function read_config_file($pathname)
    {
        $config = parse_ini_file($pathname, TRUE);

        return $config;
    }


    /**
     * Read the site config from /config/tdor.ini.
     *
     * @return array                An array containing the contents of the ini file.
     */
    function get_config()
    {
        $ini_file_pathname = get_root_path().CONFIG_FILE_PATH;

        if (file_exists($ini_file_pathname) )
        {
            return read_config_file($ini_file_pathname);
        }
        return null;
    }


    /**
     * Verify a v2 recaptcha
     *
     * See https://www.kaplankomputing.com/blog/tutorials/recaptcha-php-demo-tutorial/ for details.
     *
     * @param string $captcha_response  The response received after the recaptcha was entered
     * @param string $secret_key        The secret key for the site
     * @return boolean                  true if verified; false otherwise.
     */
    function verify_recaptcha_v2($captcha_response, $secret_key)
    {
        $captcha_ok         = !empty($captcha_response);

        if ($captcha_ok)
        {
            // Verify the captcha - see https://www.kaplankomputing.com/blog/tutorials/recaptcha-php-demo-tutorial/
            //
            $verify_url = 'https://www.google.com/recaptcha/api/siteverify';

            $data = array(
                'secret'        => $secret_key,
                'response'      => $captcha_response
            );

            $options = array(
                'http' => array (
                    'method'    => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content'   => http_build_query($data)
                )
            );

            $context        = stream_context_create($options);
            $verify         = file_get_contents($verify_url, false, $context);

            $captcha_result = json_decode($verify);

            if ($captcha_result->success == false)
            {
                // Verification failed
                $captcha_ok = false;
            }
        }
        return $captcha_ok;
    }

?>
