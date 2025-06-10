<?php

declare(strict_types = 1);

namespace Aether\Security;

use Config\Security as SecurityConfig;

/** 
 * CSRF Library
 * 
 * @class Aether\Security
**/

class CSRF
{
    public static string $csrfHash = '';

    //======================================================================================================

    public static function generate(): string
    {
        // generate csrf hash
        $randomBytes    = random_bytes(4);
        self::$csrfHash =  bin2hex($randomBytes);

        // return
        return self::$csrfHash;
    }

    //======================================================================================================

    public static function set(): void
    {
        $config = new SecurityConfig();

        // get hash and create if empty
        if (empty(self::$csrfHash))
        {
            self::generate();
        }

        // set cookie
        $expiration = time() + $config->expires;

        // new
        setcookie($config->cookieName, self::$csrfHash, );
    }

    //======================================================================================================
}