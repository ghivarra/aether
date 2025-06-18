<?php

declare(strict_types = 1);

// used library
use Aether\Exception\SystemException;
use Aether\Security\CSRF;
use Aether\View\Template;
use Laminas\Escaper\Escaper;
use Aether\Session;
use Predis\Client as RedisClient;
use Config\Cookie;
use Config\Security;

// functions
if (!function_exists('csrfToken'))
{
    /** 
     * Get csrf token name
     * 
     * @return string
     * 
    **/
    function csrfToken(): string
    {
        $security = new Security();

        // return
        return $security->tokenName;
    }
}

if (!function_exists('csrfHeader'))
{
    /** 
     * Get csrf header name
     * 
     * @return string
     * 
    **/
    function csrfHeader(): string
    {
        $security = new Security();

        // return
        return $security->headerName;
    }
}

if (!function_exists('csrfHash'))
{
    /** 
     * Get csrf hash
     * 
     * @return string
     * 
    **/
    function csrfHash(): string
    {
        // set csrf
        CSRF::set();

        // return
        return CSRF::getHash();
    }
}

if (!function_exists('console_log'))
{
    function console_log(string $message): void
    {
        echo "\n{$message}\n\n";
    }
}

if (!function_exists('dd'))
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
        if (!function_exists('d'))
        {
            require_once __DIR__ . '/kint.phar';

        } 
        
        /** @disregard P1010 Undefined function */
        d($data);
        exit(0);
    }
}

if (!function_exists('debug'))
{
    /** 
     * Debug all kind of data using Kint but not using die
     * 
     * @param mixed $data
     * 
     * @return void
     * 
    **/
    function debug(mixed $data): void
    {
        if (!function_exists('d'))
        {
            require_once __DIR__ . '/kint.phar';

        } 
        
        /** @disregard P1010 Undefined function */
        d($data);
    }
}

if (!function_exists('decrypt'))
{
    /** 
     * Decrypt string data with supplied key and selected hash + encrypt algorithm
     * 
     * @param string $data
     * @param string $key
     * @param string $hashAlgo
     * @param string $encryptAlgo
     * 
     * @return string
     * 
    **/
    function decrypt(string $data, string $key, string $hashAlgo = 'sha256', string $encryptAlgo = 'AES-256-CBC'): string
    {
        // divide salt and crypted data
        $data    = base64_decode($data);
        $salt    = substr($data, 0, 16);
        $crypted = substr($data, 16);

        // set hashing config
        $rounds   = 3;
        $password = $key . $salt;
        $hashData = [];

        // start hashing
        $hashData[0] = hash($hashAlgo, $password, true);
        $result      = $hashData[0];

        for ($i=1; $i < $rounds; $i++)
        { 
            $hashData[$i] = hash($hashAlgo, $hashData[$i - 1].$password, true);
            $result      .= $hashData[$i];
        }

        $key = substr($result, 0, 32);
        $iv  = substr($result, 32, 16);

        // return data
        return openssl_decrypt($crypted, $encryptAlgo, $key, 1, $iv);
    }
}

if (!function_exists('encrypt'))
{
    /** 
     * Encrypt string data with supplied key and selected hash + encrypt algorithm
     * 
     * @param string $data
     * @param string $key
     * @param string $hashAlgo
     * @param string $encryptAlgo
     * 
     * @return string
     * 
    **/
    function encrypt(string $data, string $key, string $hashAlgo = 'sha256', string $encryptAlgo = 'AES-256-CBC'): string
    {
        // generate salt
        $salt   = random_bytes(16);
        $salted = '';
        $dx     = '';

        // salt (16) the 256 bit key (32) into (48) char
        while (strlen($salted) <= 48)
        {
            $dx      = hash($hashAlgo, $dx . $key . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32, 16);

        // encrypt data
        $encryptedData = openssl_encrypt($data, $encryptAlgo, $key, 1, $iv);

        // return encrypted data with salt
        return base64_encode($salt . $encryptedData);
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

if (!function_exists('random_string'))
{
    /**
     * Generates a random alphanumeric string.
     *
     * @param string $type The type of string to generate: 'alpha', 'numeric', or 'alphanumeric'.
     * @param int $length The desired length of the generated string.
     * @return string The randomly generated string.
     */
    function random_string(string $type, int $length): string
    {
        $characters = '';
        switch ($type) {
            case 'alpha':
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'numeric':
                $characters = '0123456789';
                break;
            case 'alphanumeric':
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                // Default to alphanumeric if an invalid type is provided
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $randomString = '';
        $max          = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[random_int(0, $max)];
        }

        // return
        return $randomString;
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

if (!function_exists('session'))
{
    /** 
     * start session
     * 
     * On 'file' driver, there are no constructor options or variable.
     * On 'database' driver, the options is the DB Connection that has been set on Config\Database.
     * On 'redis' driver, the options is the RedisClient connection.
     * 
     * @return void
     * 
    **/
    function session(RedisClient|string|null $options = null): void
    {
        Session::start($options);
    }
}

if (!function_exists('set_cookie'))
{
    /** 
     * Send Cookie based on the Config\Cookie configurations
     * 
     * @return void
     * 
    **/
    function set_cookie(string|array $name, string $value = '', int|null $expires = null, string|null $path = null, string|null $domain = null, bool|null $secure = null, bool|null $httponly = null): void
    {
        $config = new Cookie();

        if (is_array($name))
        {
            $options = $name;

            // set cookie options
            if (!isset($options['name'], $options['value']))
            {
                $message = (AETHER_ENV === 'development') ? "Name and value should be set on set_cookie function." : "Failed to send response.";
                throw new SystemException($message, 500);
            }

            $name     = $options['name'];
            $value    = $options['value'];
            $expires  = isset($options['expires']) ? $options['expires'] : $expires;
            $path     = isset($options['path']) ? $options['path'] : $path;
            $domain   = isset($options['domain']) ? $options['domain'] : $domain;
            $secure   = isset($options['secure']) ? $options['secure'] : $secure;
            $httponly = isset($options['httponly']) ? $options['httponly'] : $httponly;
        }

        // use config if null
        $expires  = is_null($expires) ? $config->expires : $expires;
        $path     = is_null($path) ? $config->path : $path;
        $domain   = is_null($domain) ? $config->domain : $domain;
        $secure   = is_null($secure) ? $config->secure : $secure;
        $httponly = is_null($httponly) ? $config->httponly : $httponly;

        // mutate name using config prefix
        $name    = $config->prefix . $name;
        $expires = time() + $expires;

        // set cookie
        setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
    }
}

if (!function_exists('str_contains'))
{
    function str_contains(string $haystack, string $needle)
    {
        if (function_exists('mb_strpos'))
        {
            return $needle !== '' && mb_strpos($haystack, $needle) !== false;

        } else {

            return $needle !== '' && strpos($haystack, $needle) !== false;
        }        
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