<?php namespace Aether;

/** 
 * Route
 * 
 * @class Aether\Startup
**/

use Config\App;
use Dotenv\Dotenv;
use Aether\Routing;

class Startup
{
    /** 
    * Run the Aether framework
    * 
    * @return void
    **/
    public function run(): void
    {
        // load dotenv
        $env = Dotenv::createImmutable(ROOTPATH);
        $env->load();

        // route request
        $routing     = new Routing();
        $routeResult = $routing->parse($_SERVER['REQUEST_URI']);

        dd($routeResult);
    }

    //====================================================================================
}