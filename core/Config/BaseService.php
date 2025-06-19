<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Cache;
use Aether\Encryption;
use Aether\Response;
use Aether\Request;
use Aether\Routing;
use Aether\Validation;
use Aether\Debugger;
use Config\App as AppConfig;
use Config\Cache as CacheConfig;
use Config\Cookie as CookieConfig;
use Config\Database as DatabaseConfig;
use Config\Redis as RedisConfig;
use Config\Security as SecurityConfig;
use Config\Session as SessionConfig;

class BaseService
{
    private static array $sharedInstances = [];

    //===========================================================================================

    public static function getSharedInstance(string $service = '', bool $useConfig = false)
    {
        // check if stored in class and call
        if (!isset($sharedInstances[$service]))
        {
            self::$sharedInstances[$service] = ($useConfig) ? self::$service(null, false) : self::$service(false);
        }

        // return
        return self::$sharedInstances[$service];
    }

    //===========================================================================================

    public static function cache(CacheConfig|null $config = null, bool $getShared = true): Cache
    {
        if (!is_null($config))
        {
            return new Cache($config);

        } elseif ($getShared) {

            return self::getSharedInstance('cache', true);
        }

        // return
        return new Cache();
    }

    //===========================================================================================

    public static function debugger(bool $getShared = true): Debugger
    {
        if ($getShared)
        {
            return self::getSharedInstance('debugger');
        }

        // return
        return new Debugger();
    }

    //===========================================================================================

    public static function encryption(AppConfig|null $config = null, bool $getShared = true): Encryption
    {
        if (!is_null($config))
        {
            return new Encryption($config);

        } elseif ($getShared) {
            
            return self::getSharedInstance('encryption', true);
        }

        // return
        return new Encryption();
    }

    //===========================================================================================

    public static function response(bool $getShared = true): Response
    {
        if ($getShared)
        {
            return self::getSharedInstance('response');
        }

        // return
        return new Response();
    }

    //===========================================================================================

    public static function request(CookieConfig|null $config = null, bool $getShared = true): Request
    {
        if (!is_null($config))
        {
            return new Request($config);

        } elseif ($getShared) {
            
            return self::getSharedInstance('request', true);
        }

        // return
        return new Request();
    }

    //===========================================================================================

    public static function routing(bool $getShared = true): Routing
    {
        if ($getShared)
        {
            return self::getSharedInstance('routing');
        }

        // return
        return new Routing();
    }

    //===========================================================================================

    public static function validation(AppConfig|null $config = null, bool $getShared = true): Validation
    {
        if (!is_null($config))
        {
            return new Validation($config);

        } elseif ($getShared) {
            
            return self::getSharedInstance('validation', true);
        }

        // return
        return new Validation();
    }

    //===========================================================================================

    public static function appConfig(array|null $config = null, bool $getShared = true): AppConfig
    {
        $service = 'appConfig';

        if (!is_null($config))
        {
            // rewrite static
            self::$sharedInstances[$service] = new AppConfig($config);
            
            // return the shared instances
            return self::$sharedInstances[$service];
        }

        if ($getShared)
        {
            return self::getSharedInstance($service, true);
        }

        // return
        return new AppConfig();
    }

    //===========================================================================================

    public static function cacheConfig(array|null $config = null, bool $getShared = true): CacheConfig
    {
        $service = 'cacheConfig';

        if (!is_null($config))
        {
            // rewrite static
            self::$sharedInstances[$service] = new CacheConfig($config);
            
            // return the shared instances
            return self::$sharedInstances[$service];
        }

        if ($getShared)
        {
            return self::getSharedInstance($service, true);
        }

        // return
        return new CacheConfig();
    }

    //===========================================================================================

    public static function cookieConfig(array|null $config = null, bool $getShared = true): CookieConfig
    {
        $service = 'cookieConfig';

        if (!is_null($config))
        {
            // rewrite static
            self::$sharedInstances[$service] = new CookieConfig($config);
            
            // return the shared instances
            return self::$sharedInstances[$service];
        }

        if ($getShared)
        {
            return self::getSharedInstance($service, true);
        }

        // return
        return new CookieConfig();
    }

    //===========================================================================================

    public static function databaseConfig(array|null $config = null, bool $getShared = true): DatabaseConfig
    {
        $service = 'databaseConfig';

        if (!is_null($config))
        {
            // rewrite static
            self::$sharedInstances[$service] = new DatabaseConfig($config);
            
            // return the shared instances
            return self::$sharedInstances[$service];
        }

        if ($getShared)
        {
            return self::getSharedInstance($service, true);
        }

        // return
        return new DatabaseConfig();
    }

    //===========================================================================================

    public static function redisConfig(array|null $config = null, bool $getShared = true): RedisConfig
    {
        $service = 'redisConfig';

        if (!is_null($config))
        {
            // rewrite static
            self::$sharedInstances[$service] = new RedisConfig($config);
            
            // return the shared instances
            return self::$sharedInstances[$service];
        }

        if ($getShared)
        {
            return self::getSharedInstance($service, true);
        }

        // return
        return new RedisConfig();
    }

    //===========================================================================================

    public static function securityConfig(array|null $config = null, bool $getShared = true): SecurityConfig
    {
        $service = 'securityConfig';

        if (!is_null($config))
        {
            // rewrite static
            self::$sharedInstances[$service] = new SecurityConfig($config);
            
            // return the shared instances
            return self::$sharedInstances[$service];
        }

        if ($getShared)
        {
            return self::getSharedInstance($service, true);
        }

        // return
        return new SecurityConfig();
    }

    //===========================================================================================

    public static function sessionConfig(array|null $config = null, bool $getShared = true): SessionConfig
    {
        $service = 'sessionConfig';

        if (!is_null($config))
        {
            // rewrite static
            self::$sharedInstances[$service] = new SessionConfig($config);
            
            // return the shared instances
            return self::$sharedInstances[$service];
        }

        if ($getShared)
        {
            return self::getSharedInstance($service, true);
        }

        // return
        return new SessionConfig();
    }

    //===========================================================================================
}