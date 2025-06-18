<?php namespace App\Controller;

use App\Controller\BaseController;
use Aether\Interface\ResponseInterface;
use Config\Services;

/** 
 * Test Controller
 * 
 * @class App\Controller\TestController
**/

class TestController extends BaseController
{
    public function index(): string | ResponseInterface
    {
        $debugger = Services::debugger();
        $data     = [
            'text' => [
                'home'   => 'Hello World!',
                'footer' => 'Aether PHP Framework'
            ],
            'debug' => [
                'time'   => $debugger->getElapsedTime('s'),
                'memory' => $debugger->getMemoryUsage('mb'),
            ]
        ];

        return $this->response->view('HomeView', $data);
    }

    //==========================================================================================
}