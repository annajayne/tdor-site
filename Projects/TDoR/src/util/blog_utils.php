<?php
    /**
     * Support functions for blogpost pages.
     *
     */
    require_once('util/misc.php');                  // For raw_get_host()
    require_once('util/string_utils.php');          // For str_begins_with()
    require_once('util/markdown_utils.php');        // For get_image_filenames_from_markdown()


    /**
     * Add the host to blogpost image links.
     *
     * We use this as StackEdit needs image links to include the host.
     *
     * @param string $markdown              The markdown to check.
     * @return string                       The updated markdown.
     */
    function add_host_to_image_links($markdown)
    {
        $host = raw_get_host();

        $referenced_media_filenames = get_image_filenames_from_markdown($markdown);

        foreach ($referenced_media_filenames as $referenced_media_filename)
        {
            $components = parse_url($referenced_media_filename);

            if (!isset($components['scheme']) )
            {
                $markdown  = str_replace($referenced_media_filename, $host.'/'.$referenced_media_filename, $markdown);
                $markdown  = str_replace($host.'//', $host.'/', $markdown);
            }
        }
        return $markdown;
    }


    /**
     * Strip the host (if it's the current one) from image links.
     *
     * We use this as StackEdit needs image links to include the host, and we need to strip it off before storage.
     *
     * @param string $markdown              The markdown to check.
     * @return string                       The updated markdown.
     */
    function strip_host_from_image_links($markdown)
    {
        $host = raw_get_host();

        $referenced_media_filenames = get_image_filenames_from_markdown($markdown);

        foreach ($referenced_media_filenames as $referenced_media_filename)
        {
            if (str_begins_with($referenced_media_filename, $host) )
            {
                $markdown  = str_replace($host.'//', $host.'/', $markdown);
                $markdown  = str_replace($host, '', $markdown);
            }
        }
        return $markdown;
    }

?>