<?php
    /**
     * HTML utility functions.
     *
     */


    /**
     * Return the filenames of any images in the given HTML string.
     *
     * @param string $html      A string containing the HTML text.
     * @return array                An array of the filenames of the images it embeds.
     */
    function get_image_filenames_from_html($html)
    {
        $image_paths = [];

        $doc = new DOMDocument();

        $doc->loadHTML($html);

        $img_tags = $doc->getElementsByTagName('img');

        foreach ($img_tags as $tag)
        {
            $image_paths[] = $tag->getAttribute('src');
        }
        return $image_paths;
    }

?>