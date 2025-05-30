<?php namespace Aether;

/** 
 * Route
 * 
 * @class Aether\Startup
**/

use Config\App;
use Dotenv\Dotenv;
use Aether\Error;
use Aether\Routing;
use Aether\Exception\PageNotFoundException;
use Aether\Exception\SystemException;
use \Exception;

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
            $routing     = new Routing();
            $routeResult = $routing->parse($_SERVER['REQUEST_URI']);

            // check if controller exist
            if (!class_exists($routeResult['data']['controller']))
            {
                $message = (AETHER_ENV === 'development') ? "Controller <b>{$routeResult['data']['controller']}</b> is not found." : 'Page not found.';
                throw new PageNotFoundException($message);
            }

            // initiate controller class
            $controller = new $routeResult['data']['controller']();
            $method     = $routeResult['data']['method'];

            // check if method exist
            if (!method_exists($controller, $method))
            {
                $message = (AETHER_ENV === 'development') ? "Method <b>{$method}()</b> inside Controller <b>{$routeResult['data']['controller']}</b> is not found." : 'Page not found.';
                throw new PageNotFoundException($message);
            }

            // initiate controller
            $response = empty($routeResult['param']) ? $controller->$method() : $controller->$method(...$routeResult['param']);
            dd($response);

        } catch(Exception $e) {

            // initiate error class
            $error = new Error();
            $error->execute($e);
        }
    }

    //====================================================================================
}