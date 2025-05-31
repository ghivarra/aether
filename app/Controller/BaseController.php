<?php namespace App\Controller;

use Aether\Controller;

/** 
 * Base Controller
 * 
 * @class App\Controller\BaseController
**/

class BaseController extends Controller
{
    /** 
     * The collection of helpers that should be loaded from this base
     * 
     * @var array $helpers
    **/
    public array $helpers = [];

    /** 
     * The collection of middlewares that should be loaded before or after running the
     * method from controller that extends from this base
     * 
     * @var array $middlewares
    **/
    public array $middlewares = ['isLoggedOut'];

    /** 
     * The constructor that you can run to dynamically mutate the
     * helpers or middlewares collections
     * 
     * @constructor
    **/
    public function __construct()
    {
        // do before the main controller run
    }
}