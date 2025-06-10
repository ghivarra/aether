<?php namespace App\Controller;

use App\Controller\BaseController;
use Aether\Interface\ResponseInterface;
use Faker\Factory as FakerFactory;
use App\Model\UserModel;
use Aether\Database;
use Aether\Session;
use Aether\Redis;

/** 
 * Test Controller
 * 
 * @class App\Controller\TestController
**/

class TestController extends BaseController
{
    public function index(): string | ResponseInterface
    {
        $data = [
            'key1' => $this->request->get('key1'),
            'key2' => $this->request->get('key2'),
            'url'  => 'https://waduh.com/aku-juga-hero?id=1&wkwk=lol',
            'text' => [
                'home'   => 'Hello World!',
                'footer' => 'This is footer',
            ]
        ];

        // connect and check redis
        $redis = Redis::connect();
        // $test1 = $redis->set('test', 1, 'EX', 10, 'NX');
        // $test2 = $redis->set('test', 1, 'EX', 100, 'NX');

        // dd([$test1->getPayload(), $test2]);

        // $data  = $redis->set('check', 'test', 'EX', 150, 'GET');
        // dd($data);
        // $redis->set('check2', 'test2', 'EX', 10, 'NX');

        // start session
        session();

        // set flash data
        // Session::tempData('flasher', 'hehehe', 5);

        dd($_SESSION);

        // return
        return $this->response->setJSON($data);
    }

    //==========================================================================================

    public function page(): string
    {
        return 'this is page';
    }

    //==========================================================================================
}