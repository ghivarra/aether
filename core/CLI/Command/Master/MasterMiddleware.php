<?php namespace Aether\CLI\Command\Master;

use Aether\Middleware;
use Aether\Interface\ResponseInterface;
use Aether\Interface\RequestInterface;

class MasterMiddleware extends Middleware
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