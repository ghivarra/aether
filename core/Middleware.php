<?php 

declare(strict_types = 1);

namespace Aether;

use Aether\Interface\MiddlewareInterface;
use Aether\Interface\ResponseInterface;
use Aether\Interface\RequestInterface;

/** 
 * Middleware
 * 
 * A base middleware to make another middlewares
 * 
 * @class Aether\Middleware
**/

class Middleware implements MiddlewareInterface
{
    /** 
     * This function run just before the execution of
     * controller and method and act as a middleware
     * between routes and controller
     * 
     * @param RequestInterface $request
     * 
     * @return void|ResponseInterface
    **/
    public function before(RequestInterface $request)
    {

    }

    //====================================================================================

    /** 
     * This function run just after the execution of
     * controller and method and act as a middleware
     * between controller and response
     * 
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * 
     * @return void|ResponseInterface
    **/
    public function after(RequestInterface $request, ResponseInterface $response)
    {

    }

    //====================================================================================
}