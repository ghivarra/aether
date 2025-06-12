<?php namespace Config;

use Aether\Interface\Config\MiddlewareInterface;
use Aether\Middleware\CSRFMiddleware;
use App\Middleware\GlobalMiddleware;
use App\Middleware\IsLoggedOutMiddleware;
use App\Middleware\IsAdminMiddleware;

/** 
 * Middleware Configurations
 * 
 * A configuration file to register the middleware
 * using aliases
 * 
 * @class Config\Middleware
**/

class Middlewares implements MiddlewareInterface
{
    /** 
     * A collection of middleware aliases to make it easy
     * for the framework searching the correct middleware that was
     * being registered from the routes
     * 
     * @param array $aliases
     * 
    **/
    public array $aliases = [
        'global'      => GlobalMiddleware::class,
        'isAdmin'     => IsAdminMiddleware::class,
        'isLoggedOut' => IsLoggedOutMiddleware::class,
        'csrf'        => CSRFMiddleware::class,
    ];

    /** 
     * A collection of middlewares (using alias) that ALWAYS RUN ON GLOBAL just before
     * the execution of controller and after the execution of controller if already 
     * registered
     * 
     * @param array $global
     * 
    **/
    public array $global = [
        'before' => [
            'csrf',
            'global'
        ],
        'after' => [
            'global'
        ]
    ];
}