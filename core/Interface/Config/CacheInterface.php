<?php namespace Aether\Interface\Config;

/** 
 * Base Cache Interface
 * 
 * @class Aether\Interface\Config
 * 
**/

interface CacheInterface
{
    public bool $useCache { get; set; }
    public string $driver { get; set; }
    public string $prefix { get; set; }
    public string $savePath { get; set; }
    public int $lockTTL { get; set; }
}