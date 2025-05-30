<?php namespace Aether;

use Config\App;
use Config\Middlewares;
use Dotenv\Dotenv;
use Aether\Error;
use Aether\Request;
use Aether\Routing;
use Aether\Exception\PageNotFoundException;
use Aether\Exception\SystemException;
use Aether\Interface\ResponseInterface;
use \Exception;

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

            // check if controller exist
            if (!class_exists($route['data']['controller']))
            {
                $message = (AETHER_ENV === 'development') ? "Controller <b>{$route['data']['controller']}</b> is not found." : 'Page not found.';
                throw new PageNotFoundException($message);
            }

            // initiate controller class
            $controller = new $route['data']['controller']();
            $method     = $route['data']['method'];

            // check if method exist
            if (!method_exists($controller, $method))
            {
                $message = (AETHER_ENV === 'development') ? "Method <b>{$method}()</b> inside Controller <b>{$route['data']['controller']}</b> is not found." : 'Page not found.';
                throw new PageNotFoundException($message);
            }

            // initiate controller
            $response = empty($route['param']) ? $controller->$method() : $controller->$method(...$route['param']);

            // check and run after middleware first
            if (!empty($routeAfterMiddleware))
            {
                dd($routeAfterMiddleware);
            }

        } catch(Exception $e) {

            // initiate error class
            $error = new Error();
            $error->execute($e);
        }
    }

    //====================================================================================

    public function runMiddleware(string $executedTime, string|array $suppliedMiddlewares)
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

            $initClass = new $class();
            $instance  = $initClass->before($request);

            // check if return a response
            // early response mode activated
            if ($instance instanceof ResponseInterface)
            {
                return $this->runResponse($instance);
                break;
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