<?php namespace Aether;

/** 
 * Route
 * 
 * @class Aether\Startup
**/

use Aether\Route;
use Config\App;
use Dotenv\Dotenv;

class Startup
{
    public function run(): void
    {
        // load dotenv
        $env = Dotenv::createImmutable(ROOTPATH);
        $env->load();

        $config = new App();
        dd($config);
    }

    //====================================================================================
}