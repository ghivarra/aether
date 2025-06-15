<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Cache;
use Aether\Response;
use Aether\Request;
use Aether\Routing;

class BaseService
{
    private static array $sharedInstances = [];

    //===========================================================================================

    public static function getSharedInstance(string $service = '')
    {
        // check if stored in class and call
        if (!isset($sharedInstances[$service]))
        {
            self::$sharedInstances[$service] = self::$service(false);
        }

        // return
        return self::$sharedInstances[$service];
    }

    //===========================================================================================

    public static function cache(bool $getShared = true): Cache
    {
        if ($getShared)
        {
            return self::getSharedInstance('cache');
        }

        // return
        return new Cache();
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

    public static function request(bool $getShared = true): Request
    {
        if ($getShared)
        {
            return self::getSharedInstance('request');
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
}