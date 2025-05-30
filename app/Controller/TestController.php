<?php namespace App\Controller;

use App\Controller\BaseController;

/** 
 * Test Controller
 * 
 * @class App\Controller\TestController
**/

class TestController extends BaseController
{
    public function index(): string
    {
        return 'hello world!';
    }

    //==========================================================================================
}