<?php

declare(strict_types = 1);

use Config\App;

if (!function_exists('base_url'))
{
    /** 
     * Convert an URI into full URL based on baseURL config
     * 
     * @param string $uri
     * 
     * @return string
     * 
    **/
    function base_url(string $uri = ''): string
    {
        // load config
        $appConfig = new App();

        // return
        return $appConfig->baseURL . $uri;
    }
}

if (!function_exists('url_title'))
{
    /**
     * Converts a string to a URL-friendly format.
     * Similar to CodeIgniter's url_title() function.
     *
     * @param string $str The input string.
     * @param string $separator The separator to use (e.g., '-', '_'). Default is '-'.
     * @param bool $lowercase Whether to convert the string to lowercase. Default is true.
     * @return string The URL-friendly string.
     */
    function url_title($str, $separator = '-', $lowercase = true)
    {
        // Convert to lowercase if specified
        if ($lowercase === true) {
            $str = strtolower($str);
        }

        // Replace non-alphanumeric characters (except the separator) with spaces
        // The pattern allows letters, numbers, and the specified separator
        // If separator is '-', it will treat it specially
        if ($separator === '-') {
            // Allow only letters, numbers, and hyphens (and convert spaces to hyphens later)
            $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        } elseif ($separator === '_') {
            // Allow only letters, numbers, and underscores (and convert spaces to underscores later)
            $str = preg_replace('/[^a-z0-9\s_]/', '', $str);
        } else {
            // For custom separators, allow alphanumeric and spaces only
            $str = preg_replace('/[^a-z0-9\s]/', '', $str);
        }

        // Replace multiple spaces with a single space
        $str = preg_replace('/\s+/', ' ', $str);

        // Replace spaces with the specified separator
        $str = str_replace(' ', $separator, $str);

        // Trim leading/trailing separators
        $str = trim($str, $separator);

        return $str;
    }
}