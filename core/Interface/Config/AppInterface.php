<?php namespace Aether\Interface\Config;

/** 
 * App Config Interface
 * 
 * @class Aether\Interface\MiddlewareConfigInterface
**/

interface AppInterface
{
    public string $env { get; set; }
    public string $baseURL { get; set; }
    public string $permittedURIChars { get; set; }
    public string $defaultLocale { get; set; }
    public string $timezone { get; set; }
}