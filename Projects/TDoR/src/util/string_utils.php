<?php
    /**
     * String utility functions.
     *
     */
    require_once('lib/random_bytes/random.php');                            // random_bytes() implementation in case we're running on < PHP 7.0


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
     * Return a random hex string of the specified length.
     *
     * @param int $num_bytes        The length in bytes of the generated value.
     * @return string               The generated hex value.
     */
    function get_random_hex_string($num_bytes = 4)
    {
        return bin2hex(openssl_random_pseudo_bytes($num_bytes) );
    }

?>