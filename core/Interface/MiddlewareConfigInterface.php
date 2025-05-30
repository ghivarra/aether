<?php namespace Aether\Interface;

/** 
 * Middleware Config Interface
 * 
 * @class Aether\Interface\MiddlewareConfigInterface
**/

interface MiddlewareConfigInterface
{
    public array $aliases { set; get; }
    public array $global { set; get; }
}