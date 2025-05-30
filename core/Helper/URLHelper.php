<?php

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