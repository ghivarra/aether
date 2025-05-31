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
        $route->get('page/news/(:segment)/(:any)', TestController::class, 'index')
              ->as('page.news')
              ->middlewares(['isLoggedOut', 'isAdmin'], 'before')
              ->middlewares(['isLoggedOut'], 'after');

        $route->get('page/news/(:segment)', TestController::class, 'index')
              ->as('page.category');

        $route->match(['options', 'post', 'get'], '/', TestController::class, 'index')->as('home');
    }

    //==========================================================================================
}