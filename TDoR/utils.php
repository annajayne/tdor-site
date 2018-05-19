<?php

    // https://stackoverflow.com/questions/7409512/new-line-to-paragraph-function/7409591#7409591

    function nl2p($string, $line_breaks = true, $xml = true)
    {
        $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

        // It is conceivable that people might still want single line-breaks
        // without breaking into a new paragraph.
        if ($line_breaks == true)
            return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'), trim($string)).'</p>';
        else
            return '<p>'.preg_replace(
            array("/([\n]{2,})/i", "/([\r\n]{3,})/i","/([^>])\n([^<])/i"),
            array("</p>\n<p>", "</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'),

            trim($string) ).'</p>';
    }


    function nl2p2($string)
    {
        $paragraphs = '';

        foreach (explode("\n", $string) as $line)
        {
            if (trim($line) )
            {
                $paragraphs .= '<p>' . $line . '</p>';
            }
        }

        return $paragraphs;
    }


    // Mash-up version of nl2p2() which also turns basic markdown into HTML for display.
    //
    // The following markdown constructs are handled:
    //
    //      > <text>    - blockquote.
    //      - <text>    - unordered list.
    //
    function markdown_to_html($string)
    {
        $html = '';

        $blockquote = false;
        $unordered_list = false;

        foreach (explode("\n", $string) as $line)
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
        return $html;
    }


    // https://stackoverflow.com/questions/7409512/new-line-to-paragraph-function/7409591#7409591

    // https://gist.github.com/jasny/2000705

     /* Turn all URLs in clickable links.
     *
     * @param string $value
     * @param array  $protocols  http/https, ftp, mail, twitter
     * @param array  $attributes
     * @param string $mode       normal or all
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
                case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$protocol://$link</a>") . '>'; }, $value); break;
                case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
                case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>'; }, $value); break;
                default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
            }
        }

        // Insert all link
        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
    }

?>