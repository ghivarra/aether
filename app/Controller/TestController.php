<?php namespace App\Controller;

use App\Controller\BaseController;
use Aether\Interface\ResponseInterface;
use Faker\Factory as FakerFactory;
use App\Model\UserModel;
use Aether\Database;

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
        
        $default = 'default';
        $db      = Database::connect($default);
        $faker   = FakerFactory::create('id_ID');
        $model   = new UserModel($default);

        $insertData = [];
        $updateData = [];

        // insert data
        foreach (range(1, 20) as $key):

            array_push($insertData, [
                'name' => $faker->name(),
                'age'  => random_int(3, 18),
                'status' => $faker->randomElement(['nonaktif', 'aktif']),
            ]);

        endforeach;

        // update bulk data
        foreach (range(1, 20) as $key):

            array_push($updateData, [
                'id'   => 21 + $key,
                'name' => $faker->name(),
                'age'  => random_int(3, 18),
            ]);

        endforeach;

        // get data
        $getData = $model->select(['age', 'status'])
                         ->selectCount('id', 'total')
                         ->whereIn('age', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11])
                         ->groupBy(['age', 'status'])
                         ->having('status', '=', 'aktif')
                         ->update([
                            'status' => 'aktif'
                         ]);

        // insert data
        $insertion = $model->update($updateData);

        dd([$getData, $insertion, Database::getAllQueries()]);

        // home
        return view('HomeView', $data);

        // return
        return $this->response->setJSON($data);
    }

    //==========================================================================================
}