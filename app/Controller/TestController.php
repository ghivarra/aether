<?php namespace App\Controller;

use App\Controller\BaseController;
use Aether\Interface\ResponseInterface;
use Faker\Factory as FakerFactory;
use App\Model\UserModel;
use Aether\Database;
use Aether\Session;
use Aether\Redis;
use Config\Services;
use Aether\Validation;

/** 
 * Test Controller
 * 
 * @class App\Controller\TestController
**/

class TestController extends BaseController
{
    public function index(): string | ResponseInterface
    {
        return view('HomeView', [
            'text' => [
                'home'   => 'Hello World!',
                'footer' => 'MyFooter'
            ]
        ]);

        $data = [
            'id'    => 1,
            'name'  => 'Ghivarra Senandika',
            'age'   => 30,
            'avg'   => 26,
            'email' => 'gsenandika@gmail.com',
        ];

        $rules = [
            'id' => [
                'label' => 'ID',
                'rules' => ['required', 'is_unique[user.id]', 'greater_than[20]'],
            ],
            'name' => [
                'label' => 'Nama',
                'rules' => 'required|empty|exact_length[5]|not_exact_length[1]|not_exact_length_in[2]|max_length[10]|min_length[5]|equal[Marko Kesler]|not_equal[Ghivarra Senandika]|in_list[Marko Kesler, Dudu]|not_in_list[Ghivarra Senandika, Hero]',
                'error' => [
                    'equal' => '{label} Ngaranna beda euy! Kuduna {param}'
                ]
            ],
            'age' => [
                'label' => 'Umur',
                'rules' => 'required|greater_than[32]|greater_than_or_equal_to[4]|less_than[2]|less_than_or_equal_to[1]|differ[avg]|match[avg]|exact_length_with[avg]|not_exact_length_with[avg]',
            ],
            'avg' => [
                'label' => 'Rata-rata',
                'rules' => 'greater_than_field[age]|greater_than_or_equal_to_field[age]|less_than_field[age]|less_than_or_equal_to_field[age]|valid_cc_number|is_not_unique[user.id]'
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'alpha|alpha_space|alpha_numeric|alpha_numeric_dash|alpha_numeric_punct|alpha_numeric_space|string|decimal|hex|integer|is_natural_number|is_natural_number_not_zero|numeric|regex_match[/\\A[\\-+]?\\d*\\.?\\d+\\z/]|valid_timezone|valid_base64|valid_json|valid_email|valid_emails|valid_ip|valid_url|valid_url_strict|valid_date'
            ]
        ];

        $validation = new Validation();
        $validation->run($data, $rules);

        $send = [
            'errors' => $validation->getErrors(),
            'usage'  => round(memory_get_peak_usage() / 1000000, 2),
        ];

        // return
        return $this->response->setJSON($send);
    }

    //==========================================================================================

    public function page(): string | ResponseInterface
    {
        $data = [
            'file' => $this->request->files('file')
        ];

        $validation = new Validation();
        $validation->run($data, [
            'file' => [
                'label' => 'KTP',
                'rules' => 'uploaded|is_image|max_size[4]|mime_in[image/webp,image/png]|ext_in[webp,png]|max_dims[40,20]|min_dims[10000,15000]'
            ]
        ]);

        return $this->response->setJSON($validation->getErrors());
    }

    //==========================================================================================
}