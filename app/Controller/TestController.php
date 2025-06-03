<?php namespace App\Controller;

use App\Controller\BaseController;
use Aether\Interface\ResponseInterface;
use Aether\Database;
use Faker\Factory as FakerFactory;

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
        
        $db     = Database::connect();
        $status = ['aktif', 'nonaktif'];
        $faker  = FakerFactory::create('id_ID');
        $data   = [];

        foreach(range(0, 4) as $i):

            $data[$i] = [
                'id'     => 11 + $i,
                'name'   => $faker->name(),
                'age'    => $faker->numberBetween(1, 17),
                // 'status' => $status[$faker->numberBetween(0, 1)],
            ];

        endforeach;

        // builder
        $builder = $db->table('user')
                      ->updateBulk($data, 'id');

        dd($builder);

        // home
        return view('HomeView', $data);

        // return
        return $this->response->setJSON($data);
    }

    //==========================================================================================
}