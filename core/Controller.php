<?php 

declare(strict_types = 1);

namespace Aether;

use Aether\Interface\ControllerInterface;
use Aether\Interface\RequestInterface;
use Aether\Interface\ResponseInterface;
use Aether\Request;
use Aether\Response;

/** 
 * The very base of the Controller
 * 
 * @class Aether\Controller
**/

class Controller implements ControllerInterface
{
    /** 
     * Request and response variables
     * 
     * @var RequestInterface $request
     * @var ResponseInterface $response
    **/
    public RequestInterface $request;
    public ResponseInterface $response;

    /** 
     * The collection of helpers that should be loaded from this base
     * 
     * @var array $helpers
    **/
    public array $helpers = [];

    /** 
     * The collection of middlewares that should be loaded before or after running the
     * method from controller that extends from this base
     * 
     * @var array $middlewares
    **/
    public array $middlewares = [];

    /** 
     * The very function to load all of the controllers function first
     * such as $request and $response
     * 
     * @return void
    **/
    public function __loadController(): void
    {
        $this->request  = new Request();
        $this->response = new Response();
    }

    //====================================================================================================
}