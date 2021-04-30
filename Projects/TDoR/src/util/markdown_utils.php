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


?>