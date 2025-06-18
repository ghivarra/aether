<?php 

declare(strict_types = 1);

namespace Config;

/** 
 * Routes Configurations
 * 
 * @class Config\Routes
**/

use Aether\Interface\RoutingInterface;
use App\Controller\TestController;

class Routes
{
    /** 
     * Routes config run function
     * 
     * With the exception of http method, ALL ROUTES PARAMETER IS CASE SENSITIVE!
     * 
     * use (:segment) if you wanted to match only segmented URI
     * use (:any) if you wanted to match the rest of URI
     * 
     * @param RoutingInterface $route
     * 
     * @return void
    **/
    public function run(RoutingInterface $route): void
    {
        $route->all('/', TestController::class, 'index')->as('home');
    }

    //==========================================================================================
}