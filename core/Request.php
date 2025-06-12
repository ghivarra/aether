<?php 

declare(strict_types = 1);

namespace Aether;

use Aether\Interface\RequestInterface;
use Config\Cookie;

/** 
 * Request
 * 
 * Request class to retrieve incoming data from GET, POST, or JSON/PHP://input
 * 
 * @class Aether\Request
 * 
**/

class Request implements RequestInterface
{
    private static string $requestType = ''; // between cli, ajax, and web
    private static array $incomingHeaders = [];
    private static array $serverData = [];

    //===========================================================================================

    public function __construct()
    {
        static::$serverData = $_SERVER;

        foreach (static::$serverData as $key => $data):

            $prefix = substr($key, 0, 5);

            if ($prefix === 'HTTP_')
            {
                // create new key witout HTTP_
                $newKey = substr($key, 5);

                // store on static
                static::$incomingHeaders[$newKey] = $data;
            }

        endforeach;

        // set request type
        if (strtoupper(php_sapi_name()) === 'CLI')
        {
            self::$requestType = 'cli';

        } else {

            if (isset(self::$serverData['HTTP_X_REQUESTED_WITH']) && strtolower(self::$serverData['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            {
                self::$requestType = 'ajax';

            } else {

                self::$requestType = 'web';
            }
        }
    }

    //===========================================================================================

    /** 
     * Retrieve data from cookie
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     * 
    **/
    public function cookie(string $key, mixed $default = null, bool $usePrefix = true): mixed
    {
        // mutate key based on config
        $config = new Cookie();

        if ($usePrefix)
        {
            $key = $config->prefix . $key;
        }
        
        // return
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    //===========================================================================================

    /** 
     * Retrieve singular data from $_FILES
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     * 
    **/
    public function file(string $key, mixed $default = null): mixed
    {
        return isset($_FILES[$key]) ? $_FILES[$key] : $default;
    }

    //===========================================================================================

    /** 
     * Retrieve multiple data from $_FILES
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     * 
    **/
    public function files(string $key, mixed $default = null): mixed
    {
        if (!isset($_FILES[$key]))
        {
            return $default;
        }

        // iterate files to make it easier
        // to process
        $files = $_FILES[$key];
        $keys  = array_keys($files['tmp_name']);
        $data  = [];

        // iteration
        foreach ($keys as $n):

            foreach ($files as $key => $file):

                $data[$n][$key] = $file[$n];

            endforeach;

        endforeach;

        // return data
        return $data;
    }

    //===========================================================================================
    /** 
     * Retrieve data from $_GET and return the supplied $default param if
     * not exist
     * 
     * @param string $key
     * @param mixed $default
     * 
     * @return mixed
     * 
    **/
    public function get(string $key, mixed $default = null): mixed
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    //===========================================================================================

    public function header(string $key, string|null $default = null): string|null
    {
        // mutate key
        $key = str_replace('-', '_', $key);

        // return as string|null
        return isset(self::$incomingHeaders[$key]) ? self::$incomingHeaders[$key] : $default;
    }

    //===========================================================================================

    public function headers(): array
    {
        return self::$incomingHeaders;
    }

    //===========================================================================================

    public function input(mixed $default = null): mixed
    {
        // get input data
        $input = file_get_contents('php://input');
        
        // return as array/string
        return isset($input) ? $input : $default;
    }

    //===========================================================================================

    public function json(mixed $default = null): mixed
    {
        // get input data
        $input = file_get_contents('php://input');
        
        // return as array/string
        return json_validate($input) ? json_decode($input, true) : $default;
    }

    //===========================================================================================

    public function post(string $key, mixed $default = null): mixed
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    //===========================================================================================

    public function requestType(): string
    {
        return self::$requestType;
    }

    //===========================================================================================

    public function server(string $key, mixed $default = null): mixed
    {
        return isset(self::$serverData[$key]) ? self::$serverData[$key] : $default;
    }

    //===========================================================================================
}