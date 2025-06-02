<?php namespace App\Controller;

use App\Controller\BaseController;
use Aether\Interface\ResponseInterface;
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

        $db      = Database::connect();
        $builder = $db->table('post')
                      ->select(['post.id', 'post.title', 'post.view', 'post.user_id', 'user.name', 'user.age'])
                      ->join('user', 'user_id = user.id')
                      ->where('user_id', '=', 0)
                      ->orWhereNotNull('user_id')
                      ->whereNotNull('view')
                      //->get();
                      ->get()
                      ->getResultArray();

        dd($builder);

        // home
        return view('HomeView', $data);

        // return
        return $this->response->setJSON($data);
    }

    //==========================================================================================
}