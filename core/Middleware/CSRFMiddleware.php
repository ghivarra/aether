<?php 

declare(strict_types = 1);

namespace Aether\Middleware;

use Aether\Interface\ResponseInterface;
use Aether\Interface\RequestInterface;
use Aether\Middleware;
use Aether\Security\CSRF;
use Aether\Exception\SystemException;

/** 
 * CSRF Middleware
 * 
 * A middleware to execute CSRF
 * 
 * @class Aether\Middleware
**/

class CSRFMiddleware extends Middleware
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
        $filteredMethods = [
            'POST', 'PUT', 'PATCH', 'DELETE'
        ];

        $method = strtoupper($request->server('REQUEST_METHOD', 'GET'));

        // if in array the validate csrf
        if (in_array($method, $filteredMethods))
        {
            $csrf   = new CSRF();
            $result = $csrf->validate($request);

            if (!$result)
            {
                $message = (AETHER_ENV === 'development') ? "CSRF mismatched. Method is not allowed." : "The request is not allowed";
                throw new SystemException($message, 403);
            }
        }
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