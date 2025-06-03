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

        $db      = Database::connect();
//        $builder = $db->table('post')
//                      ->select(['post.user_id', 'view'])
//                      ->selectCount('post.id', 'total')
//                      ->join('user', 'user_id = user.id')
//                      ->groupStart()
//                            ->where('user_id', '!=', 0)
//                            ->whereNotNull('title')
//                      ->groupEnd()
//                      ->orGroupStart()
//                            ->where('user_id', '<', 5)
//                            ->whereNotNull('view')
//                      ->groupEnd()
//                      ->groupBy(['view'])
//                      //->get();
//                      ->get()
//                      ->getResultArray();

//        $builder = $db->table('post')
//                      ->select(['wkwk_post.id', 'title', 'user.name'])
//                      ->innerJoin('user', ['user_id = user.id AND user.status = ?', ['aktif']])
//                      ->where('view', '<>', 20)
//                      ->whereNotNull('post.title')
//                      ->orderBy('title', 'DESC')
//                      ->limit(2)
//                      ->offset(0)
//                      ->get()
//                      ->getResultArray();

        $status = ['aktif', 'nonaktif'];
        $faker  = FakerFactory::create('id');
        $data   = [
            'name'   => $faker->name(),
            'age'    => $faker->numberBetween(1, 17),
            'status' => $status[$faker->numberBetween(0, 1)],
        ];

        $builder = $db->table('user')
                      ->replace([
                        ['name', "`name`", "CONCAT(`name`, 'hahahaha')", true],
                        ['age', 5, 8],
                      ]);

        dd($builder);

        // home
        return view('HomeView', $data);

        // return
        return $this->response->setJSON($data);
    }

    //==========================================================================================
}