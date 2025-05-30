<?php namespace Aether\Interface;

/** 
 * Base Controller Interface
 * 
 * @class Aether\Interface\BaseControllerInterface
**/

interface BaseControllerInterface
{
    public array $helpers {get; set;}
    public array $middlewares {get; set;}
}