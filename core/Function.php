<?php

// used library
use Aether\Interface\ResponseInterface;
use Aether\Exception\SystemException;
use Aether\View\Template;

// functions
if (!function_exists('dd') && function_exists('d'))
{
    /** 
     * Debug all kind of data using Kint
     * 
     * @param mixed $data
     * 
     * @return void
     * 
    **/
    function dd(mixed $data): void
    {
        d($data);
        exit(0);
    }
}

if (!function_exists('getDotEnv'))
{
    /** 
     * Get data from dotenv config
     * 
     * @param string $key
     * @param 'string'|'json'|'bool'|'int'|'float'|'null' $expectedType
     * @param mixed $default
     * 
     * @return mixed
     * 
    **/
    function getDotEnv(string $key, string $expectedType = 'string', mixed $default = ''): mixed
    {
        if (!isset($_ENV[$key]))
        {
            return $default;
        }

        // get variable
        $data = $_ENV[$key];

        // check expected type
        // and modify data
        switch ($expectedType) {
            case 'json':
                $data = json_decode($data, true);
                break;

            case 'bool':
                $data = (strtolower($data) === 'true');
                break;

            case 'int':
                $data = intval($data);
                break;

            case 'float':
                $data = floatval($data);
                break;

            case 'null':
                $data = (strtolower($data) === 'null') ? null : $data;
                break;
            
            default:
                // do nothing
                break;
        }

        // return data
        return $data;
    }
}

if (!function_exists('helper'))
{
    /** 
     * Load a helper file that contain usable functions
     * 
     * @param string $name
     * 
     * @return void
     * 
    **/
    function helper(string $name): void
    {
        // check file
        $fileName = "{$name}Helper.php";

        // load in app first then in system/core path
        if (file_exists(APPPATH . "Helper/{$fileName}"))
        {
            include_once APPPATH . "Helper/{$fileName}";
        }

        if (file_exists(SYSTEMPATH . "Helper/{$fileName}"))
        {
            include_once SYSTEMPATH . "Helper/{$fileName}";
        }
    }
}

if (!function_exists('redirect'))
{
    /** 
     * Load a helper file that contain usable functions
     * 
     * @param string $url
     * @param bool $withInputData
     * @param array $flashData
     * 
     * @return void
     * 
    **/
    function redirect(string $url, bool $withInputData = false, array $flashData = []): void
    {
        // redirect and stop php script
        header("Location: {$url}");
        exit(0);
    }
}

if (!function_exists('sanitizeURI'))
{
    /** 
     * Sanitize URI Input
     * 
     * @param string $uri
     * 
     * @return bool
     * 
    **/
    function sanitizeURI(string $uri): bool
    {
        $check = (empty($uri) && (strlen($uri) < 1)) ? false : true;

        // return
        return $check;
    }
}

if (!function_exists('view'))
{
    /** 
     * Send output from viewing template
     * 
     * @param string $filename
     * 
     * @return string
     * 
    **/
    function view(string $filePath, array $data = []): string
    {
        $fullPath = VIEWPATH . $filePath . '.php';

        if (!file_exists($fullPath))
        {
            if (AETHER_ENV === 'development')
            {
                $message = 'Templating view is not found. <b>Supplied Path: ' . $fullPath . '</b>';

            } else {

                $message = 'View not found.';
            }
                
            throw new SystemException($message, 404);
        }

        // get or store data
        if (empty($data))
        {
            if (!empty(Template::$templateData))
            {
                $data = Template::$templateData;
            }

        } else {

            // store and get merged data
            Template::setTemplateData($data);
            $data = Template::$templateData;
        }

        // extract data
        extract($data);

        // require file
        require $fullPath;

        // return output buffering
        return ob_get_clean();
    }
}