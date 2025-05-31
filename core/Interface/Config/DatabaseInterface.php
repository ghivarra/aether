<?php namespace Aether\Interface\Config;

/** 
 * Database Config Interface
 * 
 * @class Aether\Interface\Config\DatabaseInterface
**/

interface DatabaseInterface
{
    public string $defaultDB { get; set; }
    public array $default { get; set; }
}