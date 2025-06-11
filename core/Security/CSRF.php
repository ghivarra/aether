<?php

declare(strict_types = 1);

namespace Aether\Security;

use Config\Security as SecurityConfig;
use Config\Cookie as CookieConfig;
use Config\Services;

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
        $config  = new SecurityConfig();
        $cookie  = new CookieConfig();
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
}