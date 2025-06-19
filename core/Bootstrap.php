<?php

declare(strict_types = 1);

// load vendor autoload.php
require_once ROOTPATH . 'vendor/autoload.php';

// load function
require_once SYSTEMPATH . 'Function.php';

// start parsing dotenv
require_once SYSTEMPATH . 'Dotenv.php';

// use ilb
use Aether\Startup;
use Config\Services;

// load config
$configApp = Services::appConfig();

// always load URL helper
helper('URL');

/** 
* Define Environment from configurations
* It should be usually between production or development
* 
* @var string AETHER_ENV
**/
define('AETHER_ENV', $configApp->env);

// check if CLI
// if not null then must be CLI
if (isset($argv[0]) && $argv[0] === 'aether')
{
    // run the CLI App
    $app = new Aether\CLI\App();
    $app->run($argv);

} else {

    if (AETHER_ENV ===  'development')
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 'on');
        ini_set('display_startup_errors', '1');
    }

    if (isset($argv[1]))
    {
        // set data
        $_SERVER['REQUEST_METHOD'] = $argv[1];
        $_SERVER['REQUEST_URI']    = $argv[2];
    
        // parse_str on URI
        if (str_contains($argv[2], '?'))
        {
            $query = substr(strstr($argv[2], '?'), 1);
            parse_str($query, $_GET);
        }
    
        // method
        $method         = strtoupper($argv[1]);
        $methodWithForm = [
            'POST', 'PUT', 'PATCH'
        ];
    
        // set non-GET method data either using JSON or www-form-urlencoded
        // and seed the form data into input into $_POST
        if (in_array($method, $methodWithForm) && isset($argv[3]) && isset($argv[4]))
        {
            $formType = strtolower($argv[3]);
            $formData = $argv[4];
    
            if ($formType === 'application/json')
            {
                $_POST = json_decode($formData, true);
    
            } elseif ($formType === 'application/x-www-form-urlencoded') {
    
                parse_str($formData, $_POST);
            }
        }
    }

    // only run startup if the request
    // has HTTP Method
    if(isset($_SERVER['REQUEST_METHOD']))
    {
        // run the Web App
        $startup = new Startup();
        $startup->run();
    }
}

