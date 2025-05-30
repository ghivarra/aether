<?php namespace Config;

/** 
 * Routes Configurations
 * 
 * @class Config\Routes
**/

use Aether\Interface\RoutesInterface;
use App\Controller\TestController;

class Routes
{
    /** 
     * Routes config run function
     * 
     * THE ROUTES IS CASE SENSITIVE!
     * 
     * use (:segment) if you wanted to match only segmented URI
     * use (:any) if you wanted to match the rest of URI
     * 
     * @param RoutesInterface $route
     * 
     * @return void
    **/
    public function run(RoutesInterface $route): void
    {
        $route->match(['options','get'], 'page/news/(:segment)/(:any)', TestController::class, 'index')
              ->as('page.news')
              ->middlewares(['isLoggedOut', 'isAdmin']);

        $route->match(['options','get'], 'page/news/(:segment)', TestController::class, 'index')
              ->as('page.category');
    }

    //==========================================================================================
}