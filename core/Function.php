<?php

declare(strict_types = 1);

// used library
use Aether\Exception\SystemException;
use Aether\View\Template;
use Laminas\Escaper\Escaper;

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

if (!function_exists('esc'))
{
    /** 
     * Escaping any string so it can be safely
     * echo'ed into the template using Laminas Escaper
     * 
     * @param string|array $data
     * @param 'html'|'htmlAttribute'|'js'|'css'|'url' $escapeType
     * @param string $encoding
     * 
     * @return string|array
     * 
    **/
    function esc(string|array $data, string $escapeType = 'html', string $encoding = 'utf-8'): string|array
    {
        // initiate laminas escaper
        $escaper = new Escaper($encoding);

        // switch
        switch ($escapeType) {
            case 'html':
                $method = 'escapeHtml';
                break;

            case 'htmlAttribute':
                $method = 'escapeHtmlAttr';
                break;

            case 'js':
                $method = 'escapeJs';
                break;

            case 'css':
                $method = 'escapeCss';
                break;

            case 'url':
                $method = 'escapeUrl';
                break;
            
            default:
                $method = 'escapeHtml';
                break;
        }

        // check data type
        if (is_array($data))
        {
            foreach ($data as $key => $value):

                $data[$key] = is_array($value) ? esc($value, $escapeType, $encoding) : $escaper->$method($value);

            endforeach;

        } else {

            return $escaper->$method($data);
        }

        // return
        return $data;
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