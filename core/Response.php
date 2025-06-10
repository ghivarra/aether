<?php 

declare(strict_types = 1);

namespace Aether;

use Aether\Interface\ResponseInterface;
use Config\Services;

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
     * @var string $this->contentType
    **/
    public string $contentType = 'text/html';

    /** 
     * Charset to be set inside the Content-Type response headers
     * 
     * @var string $this->charset
    **/
    public string $charset = 'UTF-8';

    /** 
     * Headers to be set on the response headers
     * 
     * @var array $this->headers
    **/
    public array $headers = [
        ['key' => 'Cache-Control', 'value' => 'no-store, max-age=0, no-cache'],
    ];

    /** 
     * Data to be echoed on the response
     * 
     * @var string $this->viewData
    **/
    public string $viewData = '';

    /** 
     * HTTP Response Status Code
     * 
     * @var int $this->statusCode
    **/
    public int $statusCode = 200;

    /** 
     * Check if the response is resulted in redirection
     * 
     * @var bool $this->redirected
    **/
    public bool $redirected = false;

    /** 
     * Redirect parameter based on the route alias/name
     * 
     * @var string $this->redirectParameter
    **/
    public string $redirectParameter = '';

    /** 
     * Redirect parameter based on the URL if 
     * a valid URL or baseURL(parameter) if is not a valid URL
     * 
     * @var string $this->redirectURL
    **/
    public string $redirectURL = '/';

    /** 
     * If input data is sent to the redirected view
     * or not. MUST ENABLE COOKIE OR SESSION!
     * 
     * @var bool $this->withInputData
    **/
    public bool $withInputData = false;

    /** 
     * Flash data to be sent into redirected view
     * MUST ENABLE COOKIE OR SESSION!
     * 
     * @var string $this->flashData
    **/
    public array $flashData = [];

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
        $this->contentType = 'application/json';
        $this->viewData = json_encode($data);

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
        $this->redirected = true;
        $this->redirectURL = base_url();

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
        $this->redirectURL = base_url($route['uri']);

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
        $this->charset = $charset;

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
        $this->contentType = $contentType;

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
        $headerKeys = array_column($this->headers, 'key');

        // check if found in array
        if (in_array($key, $headerKeys))
        {
            // find key
            $i = array_search($key, $headerKeys);

            // replace
            $this->headers[$i] = [
                'key'   => $key,
                'value' => $value,
            ];

        } else {

            // push header
            array_push($this->headers, [
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
        $this->statusCode = $code;

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
        $this->viewData = $data;

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
        $this->redirectURL = $url;

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
        return $this;
    }

    //==================================================================================================
}