<?php namespace Aether;

use Aether\Interface\ResponseInterface;

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
    public array $headers = [];

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
    public string $redirectURL = '';

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
    public function route(string $routeName): ResponseInterface
    {
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
        return $this;
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
     * Set redirect based on URL if the $url parameter
     * is a valid URL or baseURL(param) if it is not
     * 
     * @param string $url
     * 
     * @return ResponseInterface
    **/
    public function to(string $url): ResponseInterface
    {
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