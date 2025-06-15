<?php namespace App\Controller;

use App\Controller\BaseController;
use Aether\Interface\ResponseInterface;
use Faker\Factory as FakerFactory;
use App\Model\UserModel;
use Aether\Database;
use Aether\Session;
use Aether\Redis;
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
        $data = [
            'key1' => $this->request->get('key1'),
            'key2' => $this->request->get('key2'),
            'url'  => 'https://waduh.com/aku-juga-hero?id=1&wkwk=lol',
            'text' => [
                'home'   => 'Hello World!',
                'footer' => 'This is footer',
            ]
        ];

        // store cache
        $cache      = Services::cache();
        $encryption = Services::encryption();
        // dd($cache->get('home3', $data));
        $cache->set('home3', $encryption->encrypt(json_encode($data)));
        // $cache->delete('home*');
        // $cache->clear();

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