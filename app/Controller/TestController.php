<?php namespace App\Controller;

/** 
 * Test Controller
 * 
 * @class App\Controller\TestController
**/

use App\Controller\BaseController;

class TestController extends BaseController
{
    public function index(): string
    {
        return 'hello world!';
    }

    //==========================================================================================
}