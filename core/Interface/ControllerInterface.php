<?php namespace Aether\Interface;

use Aether\Interface\ResponseInterface;
use Aether\Interface\RequestInterface;

/** 
 * Controller Interface
 * 
 * @class Aether\Interface\ControllerInterface
**/

interface ControllerInterface
{
    public array $helpers {get; set;}
    public array $middlewares {get; set;}
    public ResponseInterface $response {get; set;}
    public RequestInterface $request {get; set;}
    public function __loadController(): void;
}