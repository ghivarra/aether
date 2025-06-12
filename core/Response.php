<?php 

declare(strict_types = 1);

namespace Aether;

use Aether\Interface\ResponseInterface;
use Config\Services;
use Aether\Session;

/** 
 * Response
 * 
 * Response class to return the processed data
 * 
 * @class Aether\Response
**/

class Response implements ResponseInterface
{
    /** 
     * Content-Type to be set on the response headers
     * 
     * @var string self::$contentType
    **/
    public static string $contentType = 'text/html';

    /** 
     * Charset to be set inside the Content-Type response headers
     * 
     * @var string self::$charset
    **/
    public static string $charset = 'UTF-8';

    /** 
     * Headers to be set on the response headers
     * 
     * @var array self::$headers
    **/
    public static array $headers = [
        ['key' => 'Cache-Control', 'value' => 'no-store, max-age=0, no-cache'],
    ];

    /** 
     * Data to be echoed on the response
     * 
     * @var string self::$viewData
    **/
    public static string $viewData = '';

    /** 
     * HTTP Response Status Code
     * 
     * @var int self::$statusCode
    **/
    public static int $statusCode = 200;

    /** 
     * Check if the response is resulted in redirection
     * 
     * @var bool self::$redirected
    **/
    public static bool $redirected = false;

    /** 
     * Redirect parameter based on the route alias/name
     * 
     * @var string self::$redirectParameter
    **/
    public static string $redirectParameter = '';

    /** 
     * Redirect parameter based on the URL if 
     * a valid URL or baseURL(parameter) if is not a valid URL
     * 
     * @var string self::$redirectURL
    **/
    public static string $redirectURL = '/';

    /** 
     * If input data is sent to the redirected view
     * or not. MUST ENABLE COOKIE OR SESSION!
     * 
     * @var bool self::$withInputData
    **/
    public static bool $withInputData = false;

    /** 
     * Flash data to be sent into redirected view
     * MUST ENABLE COOKIE OR SESSION!
     * 
     * @var string self::$flashData
    **/
    public static array $flashData = [];

    //==================================================================================================

    /** 
     * JSON data to be converted into the response view
     * 
     * @param mixed $data
     * 
     * @return ResponseInterface
    **/
    public function json(mixed $data): ResponseInterface
    {
        // input as data as json
        self::$contentType = 'application/json';
        self::$viewData = json_encode($data);

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * Toggle response redirect on
     * 
     * @return ResponseInterface
    **/
    public function redirect(): ResponseInterface
    {
        // set redirect as true
        // and set default redirect URL
        self::$redirected = true;
        self::$redirectURL = base_url();

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * Set redirect based on route name/alias
     * 
     * @param string $routeName
     * 
     * @return ResponseInterface
    **/
    public function route(string $routeName, array $params = []): ResponseInterface
    {
        // initiate routing
        $routing = Services::routing();

        // set redirect
        $route = $routing->findByAlias($routeName, $params);

        // set url
        self::$redirectURL = base_url($route['uri']);

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * Set Charset inside Content-Type in the response header
     * 
     * @param string $charset
     * 
     * @return ResponseInterface
    **/
    public function setCharset(string $charset): ResponseInterface
    {
        // set charset
        self::$charset = $charset;

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * Set HTTP Content-Type in the response header
     * 
     * @param string $contentType
     * 
     * @return ResponseInterface
    **/
    public function setContentType(string $contentType): ResponseInterface
    {
        // set content type
        self::$contentType = $contentType;

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * Set response header
     * 
     * @param string $key
     * @param int|string $value
     * 
     * @return ResponseInterface
    **/
    public function setHeader(string $key, int|string $value): ResponseInterface
    {
        // check if header exist
        $headerKeys = array_column(self::$headers, 'key');

        // check if found in array
        if (in_array($key, $headerKeys))
        {
            // find key
            $i = array_search($key, $headerKeys);

            // replace
            self::$headers[$i] = [
                'key'   => $key,
                'value' => $value,
            ];

        } else {

            // push header
            array_push(self::$headers, [
                'key'   => $key,
                'value' => $value,
            ]);
        }

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * An alias for json()
     * 
     * @param mixed $data
     * 
     * @return ResponseInterface
    **/
    public function setJSON(mixed $data): ResponseInterface
    {
        return $this->json($data);
    }

    //==================================================================================================

    /** 
     * Set HTTP Response Status Code
     * 
     * @param int $code
     * 
     * @return ResponseInterface
    **/
    public function setStatusCode(int $code): ResponseInterface
    {
        // set status code
        self::$statusCode = $code;

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * Set View data by supplying the string based data
     * 
     * @param string $data
     * 
     * @return ResponseInterface
    **/
    public function setViewData(string $data): ResponseInterface
    {
        // set view data
        self::$viewData = $data;

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * Set redirect based on URL if the $url parameter
     * is a valid URL or baseURL(param) if it is not
     * 
     * @param string $url
     * 
     * @return ResponseInterface
    **/
    public function to(string $url): ResponseInterface
    {
        // check if not valid url, then mutate using baseURL
        if (!filter_var($url, FILTER_VALIDATE_URL))
        {
            $url = base_url($url);
        }

        // set redirected url
        self::$redirectURL = $url;

        // return
        return $this;
    }

    //==================================================================================================

    /** 
     * View based on the file path from param
     * 
     * @param string $filePath
     * 
     * @return ResponseInterface
    **/
    public function view(string $filepath): ResponseInterface
    {
        // require view path
        require VIEWPATH . $filePath;

        // return instance
        return $this;
    }

    //==================================================================================================

    /** 
     * Flash Data for redirected data
     * 
     * MUST ENABLE COOKIE OR SESSION!
     * 
     * @param string|array $key
     * @param string|null $value
     * 
     * @return ResponseInterface
    **/
    public function with(string|array $key, string|null $value): ResponseInterface
    {
        $status = session_status();

        if ($status === PHP_SESSION_NONE)
        {
            // set on cookie
            set_cookie($key, $value);
            
        } elseif ($status === PHP_SESSION_ACTIVE) {

            // set on session
            Session::flashData($key, $value);
        }

        // return instance
        return $this;
    }

    //==================================================================================================

    /** 
     * Toggle if redirected view need to supplied by
     * previous input data or not
     * 
     * MUST ENABLE COOKIE OR SESSION!
     * 
     * @return ResponseInterface
    **/
    public function withInput(): ResponseInterface
    {
        if (!empty($_POST))
        {
            $status = session_status();

            if ($status === PHP_SESSION_NONE)
            {
                // set post data on cookie
                set_cookie('__old', json_encode($_POST));
                
            } elseif ($status === PHP_SESSION_ACTIVE) {

                // set on session
                Session::flashData('__old', $_POST);
            }
        }

        // return instances
        return $this;
    }

    //==================================================================================================
}