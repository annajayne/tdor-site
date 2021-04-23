<?php
    /**
     * General utility functions.
     *
     */
    require_once('lib/tuupola/base62/src/Base62/BaseEncoder.php');
    require_once('lib/tuupola/base62/src/Base62/GmpEncoder.php');
    require_once('lib/tuupola/base62/src/Base62/PhpEncoder.php');
    require_once('lib/tuupola/base62/src/Base62.php');
    require_once('lib/random_bytes/random.php');                            // random_bytes() implementation in case we're running on < PHP 7.0
    require_once('util/ParsedownExtraImageLinksPlugin.php');
    require_once('defines.php');                                            // For CONFIG_FILE_PATH
    require_once('util/misc.php');                                          // For get_root_path()



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
     * Get the first "n" words of a string.
     *
     * @param string $longtext      The string to search in
     * @param int $wordcount        The number of words we want.
     * @return boolean              The first 'n' words of $longtext.
     */
    function get_first_n_words($longtext, $wordcount)
    {
        // remove redundant Windows CR
        $longtext = preg_replace ("/\r/", "", $longtext);

        // A space to an end, just in case
        $longtext = $longtext . " ";

        //  Regular expression for a word
        $wordpattern = "([\w\(\)\.,;?!-_«»\"\'’]*[ \n]*)";

        // Determine how many words are in the text
        $maxwords = preg_match_all ("/" . $wordpattern . "/", $longtext, $words);

        //  Make sure that the maximum number of available words is matched
        $wordcount = min($wordcount, $maxwords);

        // Create a regular expression for the desired number of words
        $pattern = "/" . $wordpattern . "{0," . $wordcount . "}/";

        // Read the desired number of words
        $match = preg_match ($pattern, $longtext, $shorttext);

        // Get the right result out of the result array
        $shorttext = $shorttext[0];

        return $shorttext;
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
     * Determine whether the given path is relative.
     *
     * @param string $path          A string containing the path.
     * @return boolean              true if the path is relative (i.e. doesn't begin with http://, https:// or a slash.
     */
    function is_path_relative($path)
    {
        if (!str_begins_with($path, 'http://') && !str_begins_with($path, 'https://') && !str_begins_with($path, '/') )
        {
            return true;
        }
        return false;
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
            return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'), trim($text) ).'</p>';
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
     * Return the filenames of any images in the given markdown string.
     *
     * @param string $markdown      A string containing the markdown text.
     * @return array                An array of the filenames of the images it embeds.
     */
    function get_image_filenames_from_markdown($markdown)
    {
        // Identify any relative links to images and replace them with site relative ones.
        //
        // See https://stackoverflow.com/questions/57964321/parsedown-get-all-image-links
        $regex = '/(^|\n)((\[.+\]: )|(!\[.*?\]\())(?<image>.+?\.[^\) ]+)?/';

        $str = preg_replace('/~~~.*?~~~/s', '', $markdown);

        preg_match_all($regex, $str, $matches, PREG_PATTERN_ORDER);

        return $matches['image'];
    }


    /**
     * Use Parsedown (and specifically the custom ParsedownExtraImageLinksPlugin) to convert markdown into HTML.
     *
     * Note that external links should have target=_blank and rel=nofollow attributes, and the markdown may
     * contain embedded HTML for embedded video (YouTube, Vimeo etc.).
     *
     * @param string $markdown              A string containing the markdown text.
     * @param string $image_links_rel_attr  The 'rel' attribute used to wrap inline image links. Used for lightbox support
     * @return string                       The corresponding HTML.
     */
    function markdown_to_html($markdown, $image_links_rel_attr = 'lightbox')
    {
        $parsedown                          = new ParsedownExtraImageLinksPlugin();

        // External links should have the rel="nofollow" and target="_blank" attributes
        $parsedown->linkAttributes = function($Text, $Attributes, &$Element, $Internal)
        {
            if (!$Internal)
            {
                return ['rel' => 'nofollow', 'target' => '_blank'];
            }
            return [];
        };

        // External links should have the rel="nofollow" and target="_blank" attributes
        $parsedown->linkAttributes = function($Text, $Attributes, &$Element, $Internal)
        {
            if (!$Internal)
            {
                return ['rel' => 'nofollow', 'target' => '_blank'];
            }
            return [];
        };

        // Generate <figure> and <figCaption> tags from images with captions
        $parsedown->figuresEnabled          = true;
        $parsedown->figureAttributes        = ['class' => 'image'];
        $parsedown->imageAttributesOnParent = ['class', 'id'];

        // Wrap inline images with links
        $parsedown->add_image_links         = !empty($image_links_rel_attr);
        $parsedown->image_links_rel_attr    = $image_links_rel_attr;
        $parsedown->image_links_target_attr = '_blank';

        // Convert the markdown
        $html                               = $parsedown->text($markdown);

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


    /**
     * A recursive version of scandir().
     *
     * Source: https://stackoverflow.com/questions/34190464/php-scandir-recursively
     *
     * @param string $folder_path       The full path of the folder
     * @return array                    An array containing the relative paths of the files
     */
    function recursive_scandir($folder_path)
    {
        $result = [];

        foreach (scandir($folder_path) as $filename)
        {
            if ($filename[0] === '.')
            {
                continue;
            }

            $filePath = $folder_path . '/' . $filename;

            if (is_dir($filePath))
            {
                foreach (recursive_scandir($filePath) as $childFilename)
                {
                    $result[] = $filename . '/' . $childFilename;
                }
            }
            else
            {
                $result[] = $filename;
            }
        }
        return $result;
    }


    if (!function_exists('write_ini_file') )
    {
        /**
         * Write an ini configuration file.
         *
         * Sourc: https://stackoverflow.com/questions/5695145/how-to-read-and-write-to-an-ini-file-with-php/5695203.
         *
         * @param string $file
         * @param array  $array
         * @return bool
         */
        function write_ini_file($file, $array = [])
        {
            // check first argument is string
            if (!is_string($file) )
            {
                throw new \InvalidArgumentException('Function argument 1 must be a string.');
            }

            // check second argument is array
            if (!is_array($array) )
            {
                throw new \InvalidArgumentException('Function argument 2 must be an array.');
            }

            // process array
            $data = array();

            foreach ($array as $key => $val)
            {
                if (is_array($val) )
                {
                    $data[] = "[$key]";
                    foreach ($val as $skey => $sval)
                    {
                        if (is_array($sval) )
                        {
                            foreach ($sval as $_skey => $_sval)
                            {
                                if (is_numeric($_skey) )
                                {
                                    $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"') );
                                }
                                else
                                {
                                    $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"') );
                                }
                            }
                        }
                        else
                        {
                            $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"') );
                        }
                    }
                }
                else
                {
                    $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"') );
                }

                // empty line
                //$data[] = null;
            }

            // open file pointer, init flock options
            $fp = fopen($file, 'w');
            $retries = 0;
            $max_retries = 100;

            if (!$fp)
            {
                return false;
            }

            // loop until get lock, or reach max retries
            do
            {
                if ($retries > 0)
                {
                    usleep(rand(1, 5000) );
                }
                $retries += 1;
            } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

            // couldn't get the lock
            if ($retries == $max_retries)
            {
                return false;
            }

            // got lock, write data
            fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

            // release lock
            flock($fp, LOCK_UN);
            fclose($fp);

            return true;
        }
    }


?>
