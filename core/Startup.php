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

            // initiate controller
            // throw new SystemException('Data tidak ditemukan', 400);
            // throw new PageNotFoundException('Halaman Tidak Ditemukan');

        } catch(Exception $e) {

            // initiate error class
            $error = new Error();
            $error->execute($e);
        }
    }

    //====================================================================================
}