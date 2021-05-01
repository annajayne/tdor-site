<?php
    /**
     * Markdown utility functions.
     *
     */
    require_once('util/ParsedownExtraImageLinksPlugin.php');


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

?>