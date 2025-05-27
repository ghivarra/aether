<?php

// used library


// functions
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