<?php 

declare(strict_types = 1);

namespace Aether;

use Config\App;
use Config\Middlewares;
use Dotenv\Dotenv;
use Aether\Error;
use Aether\Request;
use Aether\Response;
use Aether\Routing;
use Aether\Exception\PageNotFoundException;
use Aether\Interface\ResponseInterface;
use \Throwable;

/** 
 * Route
 * 
 * @class Aether\Startup
**/

class Startup
{
    /** 
    * Run the Aether framework
    * 
    * @return void
    **/
    public function run(): void
    {
        // run all inside a huge block of try/catch
        try {

            // load dotenv
            $env = Dotenv::createImmutable(ROOTPATH);
            $env->load();

            // load config
            $configApp = new App();

            // load helper
            helper('URL');

            /** 
            * Define Environment from configurations
            * It should be usually between production or development
            * 
            * @var string AETHER_ENV
            **/
            define('AETHER_ENV', $configApp->env);

            if (AETHER_ENV ===  'development')
            {
                error_reporting(E_ALL);
                ini_set('display_errors', 'on');
                ini_set('display_startup_errors', '1');
            }

            // route request
            $routing = new Routing();
            $route   = $routing->find($_SERVER['REQUEST_URI']);

            // mutate middlewares
            $routeBeforeMiddleware = $route['data']['middlewares']['before'];
            $routeAfterMiddleware  = $route['data']['middlewares']['after'];

            // run before controller middleware
            $this->runMiddleware('before', $routeBeforeMiddleware);

            // run controller
            $response = $this->runController($route['data']['controller'], $route['data']['method'], $route['param']);

            // run after controller middleware
            $this->runMiddleware('after', $routeAfterMiddleware, $response);

            // if there are no return response from the middleware 
            // then run response
            $this->runResponse($response);

        } catch(\Throwable $e) {

            // initiate error class
            $error = new Error();
            $error->execute($e);
        }
    }

    //====================================================================================

    public function runController(string $controllerClass, string $method, array $params = []): ResponseInterface
    {
        // check if controller exist
        if (!class_exists($controllerClass))
        {
            $message = (AETHER_ENV === 'development') ? "Controller <b>{$controllerClass}</b> is not found." : 'Page not found.';
            throw new PageNotFoundException($message);
        }

        // initiate controller class
        $controller = new $controllerClass();

        // check if method exist
        if (!method_exists($controller, $method))
        {
            $message = (AETHER_ENV === 'development') ? "Method <b>{$method}()</b> inside Controller <b>{$controllerClass}</b> is not found." : 'Page not found.';
            throw new PageNotFoundException($message);
        }

        // load controller features
        $controller->__loadController();

        // check middlewares & run middleware before controller method
        if (isset($controller->middlewares) && !empty($controller->middlewares))
        {
            $this->runMiddleware('before', $controller->middlewares);
        }

        // load helpers
        if (isset($controller->helpers) && !empty($controller->helpers))
        {
            foreach ($controller->helpers as $helperName):

                helper($helperName);

            endforeach;
        }

        // buffer output just before controller
        // is initialized
        ob_start();

        // init controller
        $response = empty($params) ? $controller->$method() : $controller->$method(...$params);

        // check type
        if ($response instanceof ResponseInterface)
        {
            // check middlewares & run middleware after controller method
            if (isset($controller->middlewares) && !empty($controller->middlewares))
            {
                $this->runMiddleware('after', $controller->middlewares, $response);
            }

            return $response;
        }

        // if not then initiate response
        // and set view data using the correct $response type
        $newResponse = new Response();

        if (is_string($response))
        {
            $newResponse->setViewData($response);

        } elseif (!empty($string)) {

            $newResponse->setViewData(strval($response));
        }

        // check middlewares & run middleware after controller method
        if (isset($controller->middlewares) && !empty($controller->middlewares))
        {
            $this->runMiddleware('after', $controller->middlewares, $newResponse);
        }

        // initiate and return controller
        return $newResponse;
    }

    //====================================================================================

    public function runMiddleware(string $executedTime, array $suppliedMiddlewares, ResponseInterface|null $response = null)
    {
        // load middlewares config & request
        $middleware = new Middlewares();
        $request    = new Request();

        // classes collection
        $middlewareClasses = [];

        // execute global middlewares first
        if (!empty($middleware->global[$executedTime]))
        {
            foreach ($middleware->global[$executedTime] as $alias):

                array_push($middlewareClasses, '\\' . $middleware->aliases[$alias]);

            endforeach; 
        }

        // then execute supplied middleware from
        // routes
        foreach ($suppliedMiddlewares as $alias):

            array_push($middlewareClasses, '\\' . $middleware->aliases[$alias]);

        endforeach; 

        // execute all
        foreach ($middlewareClasses as $class):

            if ($executedTime === 'before')
            {
                $initClass = new $class();
                $instance  = $initClass->before($request);
    
                // check if return a response
                // early response mode activated
                if ($instance instanceof ResponseInterface)
                {
                    return $this->runResponse($instance);
                    break;
                }

            } elseif ($executedTime === 'after') {

                $initClass = new $class();
                $instance  = $initClass->after($request, $response);
    
                // check if return a response
                // early response mode activated
                if ($instance instanceof ResponseInterface)
                {
                    return $this->runResponse($instance);
                    break;
                }
            }

        endforeach;
    }

    //====================================================================================

    public function runResponse(ResponseInterface $response): void
    {
        // check redirect
        if ($response->redirected)
        {
            // early return
            redirect($response->redirectURL);
        }

        // set status code
        http_response_code($response->statusCode);

        // set content type & charset
        header("Content-Type: {$response->contentType}; charset={$response->charset}");

        // set another headers
        foreach ($response->headers as $header):

            header("{$header['key']}: {$header['value']}");

        endforeach;

        // echo view data
        echo $response->viewData;

        // end running script
        exit(0);
    }

    //====================================================================================
}