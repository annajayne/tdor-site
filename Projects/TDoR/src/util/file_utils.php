<?php
    /**
     * URL and path utility functions.
     *
     */
    require_once('util/string_utils.php');          // For str_begins_with()


    /**
     * Recursively remove empty subfolders.
     *
     * @param string $folder_path       A string containing the path.
     */
    function remove_empty_subfolders($folder_path)
    {
        $folder_empty = true;

        foreach (glob($folder_path.DIRECTORY_SEPARATOR.'*') as $filename)
        {
            $folder_empty &= is_dir($filename) && remove_empty_subfolders($filename);
        }
        return $folder_empty && rmdir($folder_path);
    }
?>
