<?php

declare(strict_types = 1);

namespace Aether\Security;

use Config\Services;
use Aether\Interface\RequestInterface;

/** 
 * CSRF Library
 * 
 * @class Aether\Security
**/

class CSRF
{
    private static string $csrfHash = '';

    //======================================================================================================

    public static function getHash(): string
    {
        return self::$csrfHash;
    }

    //======================================================================================================

    public static function generate(): string
    {
        // generate csrf hash
        $randomBytes = random_bytes(4);

        // return
        return bin2hex($randomBytes);
    }

    //======================================================================================================

    public static function set(): void
    {
        $config  = Services::securityConfig();
        $cookie  = Services::cookieConfig();
        $request = Services::request();

        // check if always regenerate new hash
        if (empty(self::$csrfHash))
        {
            if ($config->regenerate)
            {
                self::$csrfHash = self::generate();

            } else {

                // get old hash and set new hash if empty
                self::$csrfHash = $request->cookie($config->cookieName, self::generate());
            }
        }

        // set expiration
        $expiration = time() + $config->expires;

        // set cookie
        setcookie($config->cookieName, self::$csrfHash, $expiration, $cookie->path, $cookie->domain, $cookie->secure, true);
    }

    //======================================================================================================

    public function validate(RequestInterface|null $request = null): bool
    {
        $config  = Services::securityConfig();
        $request = is_null($request) ? Services::request() : $request;
        $method  = $request->server('REQUEST_METHOD');

        if (is_null($method))
        {
            // passed because the method is not detected
            // possibly CLI
            return true;
        }

        // capitalize method
        $method = strtoupper($method);

        // get cookie first and if null get headers
        $csrfHash = $request->cookie($config->cookieName, 'no-csrf-found', false);

        // now switch case on method on how to get input hash
        switch ($method) {
            case 'POST':
                $inputHash = $request->post($config->tokenName);
                break;

            case 'PUT':
                $contentType  = $request->header('CONTENT-TYPE');
                $inputRequest = $request->input('');

                if ($contentType === 'application/json')
                {
                    $decoded = json_decode($inputRequest, true);
                    $input   = isset($decoded[0]) ? $decoded[0] : $decoded;

                } else {

                    $inputRequest = parse_str($inputRequest, $input);
                }

                $inputHash = isset($input[$config->tokenName]) ? $input[$config->tokenName] : '';
                break;

            case 'PATCH':
                $contentType  = $request->header('CONTENT-TYPE');
                $inputRequest = $request->input('');

                if ($contentType === 'application/json')
                {
                    $decoded = json_decode($inputRequest, true);
                    $input   = isset($decoded[0]) ? $decoded[0] : $decoded;

                } else {

                    $inputRequest = parse_str($inputRequest, $input);
                }

                $inputHash = isset($input[$config->tokenName]) ? $input[$config->tokenName] : '';
                break;

            case 'DELETE':
                $inputHash = $request->get($config->tokenName);
                break;
            
            default:
                // always return true if the method
                // should not use CSRF
                return true;    
                break;
        }

        // set false if not matched
        if ($inputHash !== $csrfHash)
        {
            return false;
        }

        // return
        return true;
    }

    //======================================================================================================
}