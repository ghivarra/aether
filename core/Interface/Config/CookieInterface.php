<?php namespace Aether\Interface\Config;

/** 
 * Base Cookie Interface
 * 
 * @class Aether\Interface\Config
 * 
**/

interface CookieInterface
{
    public string $prefix { get; set; }
    public int $expires { get; set; }
    public string $path { get; set; }
    public string $domain { get; set; }
    public bool $secure { get; set; }
    public bool $httponly { get; set; }
    public string $samesite { get; set; }
}