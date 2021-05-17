<?php
    /**
     * Markdown utility functions.
     *
     */
    require_once('util/ParsedownExtraImageLinksPlugin.php');        // For ParsedownExtraImageLinksPlugin
    require_once('util/html_utils.php');                            // For get_image_filenames_from_html()
    require_once('util/link_preview.php');                          // For LinkPreview


    /**
     * Expand any link previews of the form [@preview](url) within the given markdown text.
     *
     * The LinkPreview class is used to generate the link preview.
     *
     * @param string $markdown              A string containing the markdown text.
     * @return string                       The markdown text, with HTML link previews added where appropriate.
     */
    function expand_markdown_link_previews($markdown)
    {
        // Identify any link previews we need to expand
        $pattern = "/\[(.*?)\]\s?\((.*?)\)/i";

        $matches = null;
        preg_match_all($pattern, $markdown, $matches);

        //  Given:
        //      $input_lines = "[LINK1] (http://example.com)\n\n some text [LINK2](http://sub.example.com/) some more text";
        //
        //      $matches will be:
        //
        //          array(3
        //                0	=>	array(2
        //                            0	=>	[LINK1] (http://example.com)
        //                            1	=>	[LINK2](http://sub.example.com/)
        //                           )
        //                1	=>	array(2
        //                            0	=>	LINK1
        //                            1	=>	LINK2
        //                           )
        //                2	=>	array(2
        //                            0	=>	http://example.com
        //                            1	=>	http://sub.example.com/
        //                           )
        //               )
        //
        //  As such we iterate $matches[1] looking for "@preview". If found, replace the corresponding text in $array[0] text with the link preview.
        if (!empty($matches[1]) )
        {
            foreach($matches[1] as $key => $link_text)
            {
                if (stripos($link_text, '@preview') === 0)
                {
                    // $matches[0][$key] is text to search for and replace with a link preview
                    // $matches[2][$key] is the target URL
                    $markdown_link  = $matches[0][$key];
                    $url            = $matches[2][$key];
                    $preview        = new LinkPreview($url);

                    if ($preview->read_ok() )
                    {
                        $markdown   = str_replace($markdown_link, $preview->get_html(), $markdown);
                    }
                }
            }
        }
        return $markdown;
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
        $parsedown->linkAttributes          = function($Text, $Attributes, &$Element, $Internal)
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
        $html                               = $parsedown->text(expand_markdown_link_previews($markdown) );

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
        return get_image_filenames_from_html(markdown_to_html($markdown) );
    }

?>