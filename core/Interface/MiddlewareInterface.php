<?php namespace Aether\Interface;

/** 
 * Middleware Interface
 * 
 * @class Aether\Interface\MiddlewareInterface
**/

use Aether\Interface\ResponseInterface;
use Aether\Interface\RequestInterface;

interface MiddlewareInterface
{    
    public function before(RequestInterface $request);
    public function after(RequestInterface $request, ResponseInterface $response);
}