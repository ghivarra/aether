<?php namespace Aether\Interface\Config;

/** 
 * Middleware Config Interface
 * 
 * @class Aether\Interface\Config\MiddlewareInterface
**/

interface MiddlewareInterface
{
    public array $aliases { set; get; }
    public array $global { set; get; }
}