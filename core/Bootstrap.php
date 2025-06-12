<?php

declare(strict_types = 1);

// hardcode the version
define('AETHER_VERSION', file_get_contents(__DIR__ . '/VERSION.txt'));

// load vendor autoload.php
require_once ROOTPATH . 'vendor/autoload.php';

// load function
require_once SYSTEMPATH . 'Function.php';

// run startup
require_once SYSTEMPATH . 'Startup.php';

// check if CLI
// if not null then must be CLI
if (!is_null($argv) && $argv[0] === 'aether')
{
    // run the CLI App
    $app = new Aether\CLI\App();
    $app->run($argv);

} else {

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

    // run the Web App
    $startup = new Aether\Startup();
    $startup->run();
}

