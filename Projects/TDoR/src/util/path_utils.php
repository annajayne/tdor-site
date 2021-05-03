<?php
    /**
     * URL and path utility functions.
     *
     */


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
     * Add the given path to the given host, taking into account whether the path has a leading slash.
     *
     * @param string $path1          The protocol and host.
     * @param string $path2          The path.
     * @return string               The constructed url.
     */
    function append_path($path1, $path2)
    {
        if (str_begins_with($path2, '/') )
        {
            return $path1.$path2;
        }
        return "$path1/$path2";
    }


?>
