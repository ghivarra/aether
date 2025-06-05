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
        
        $db      = Database::connect('default');
        $builder = $db->table('post')
                      ->select(['post.id', 'title', 'view', 'user_id', 'user.name', 'user.age'])
                      ->whereIn('user_id', [2, 3])
                      ->whereNotIn('user_id', [1, 12])
                      ->join('user', 'user_id = user.id')
                      ->orderBy('name', 'ASC')
                      ->get()
                      ->getResultArray();

        //dd($builder);
        //dd(Database::getAllQueries());

        $status = ['aktif', 'nonaktif'];
        $faker  = FakerFactory::create('id_ID');
        $data   = [];

        foreach(range(0, 520) as $i):

            $data[$i] = [
                'name'   => $faker->name(),
                'age'    => $faker->numberBetween(1, 17),
                'status' => $status[$faker->numberBetween(0, 1)],
            ];

        endforeach;

        // update data
        $updateData = [];

        foreach(range(0, 300) as $i):

            $updateData[$i] = [
                'id'     => 20 + $i,
                'name'   => $faker->name(),
                'age'    => $faker->numberBetween(1, 17),
                // 'status' => $status[$faker->numberBetween(0, 1)],
            ];

        endforeach;

        // test performance
        // $db->table('user')->insertBulk($data);
        // $db->table('user')->updateBulk($updateData, 'id');

        // debug
        dd(Database::getAllQueries());

        // home
        return view('HomeView', $data);

        // return
        return $this->response->setJSON($data);
    }

    //==========================================================================================
}