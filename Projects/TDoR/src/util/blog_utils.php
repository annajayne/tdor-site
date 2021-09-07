<?php
    /**
     * Support functions for blogpost pages.
     *
     */
    require_once('util/misc.php');                  // For raw_get_host()
    require_once('util/string_utils.php');          // For str_begins_with()
    require_once('util/markdown_utils.php');        // For get_image_filenames_from_markdown()
    require_once('models/blog_table.php');          // For BlogTable::get_filesystem_safe_title()


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


    /**
     * Return the path of the blog content folder
     *
     * @return string                        The blog content folder.
     */
    function get_blog_content_folder()
    {
        return 'blog/content';
    }


    /**
     * Return the path of the folder which should contain the media files for the given blogpost
     *
     * @param string $content_folder_path    The path where media files for the blogpost should be stored.
     * @param Blogpost $blogpost             The blogpost.
     * @return string                        The media folder for the specified blogpost.
     */
    function get_blogpost_media_folder_path($content_folder_path, $blogpost)
    {
        $date                   = new DateTime($blogpost->timestamp);
        $date_field             = $date->format('Y_m_d');

        $title_field            = BlogTable::get_filesystem_safe_title($blogpost->title);

        $blogpost_folder_name   = $date_field.'_'.$title_field.'_'.$blogpost->uid;

        return "$content_folder_path/media/$blogpost_folder_name";
    }

?>