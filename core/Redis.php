<?php

declare(strict_types = 1);

namespace Aether;

use Config\Redis as RedisConfig;
use Predis\Client as RedisClient;

class Redis
{
    public static RedisClient|null $currentConnection = null;

    //=============================================================================================

    private static function unixConnect(RedisConfig $config): void
    {
        self::$currentConnection = new RedisClient([
            'scheme' => $config->scheme,
            'path'   => $config->path,
            'prefix' => $config->prefix,
        ]);
    }

    //=============================================================================================

    private static function tcpConnect(RedisConfig $config): void
    {
        self::$currentConnection = new RedisClient([
            'scheme' => $config->scheme,
            'path'   => $config->path,
            'port'   => $config->port,
            'prefix' => $config->prefix,
        ]);
    }

    //=============================================================================================

    private static function tlsConnect(RedisConfig $config): void
    {
        self::$currentConnection = new RedisClient([
            'scheme' => $config->scheme,
            'path'   => $config->path,
            'port'   => $config->port,
            'prefix' => $config->prefix,
            'ssl'    => [
                'cafile'      => $config->certPath,
                'verify_peer' => true,
            ]
        ]);
    }

    //=============================================================================================

    public static function connect()
    {
        if (is_null(self::$currentConnection))
        {
            $config = new RedisConfig();

            switch ($config->scheme) {
                case 'tls':
                    self::tlsConnect($config);
                    break;

                case 'unix':
                    self::unixConnect($config);
                    break;
                
                default:
                    // the default scheme is tcp
                    self::tcpConnect($config);
                    break;
            }
        }

        // return connection
        return self::$currentConnection;
    }

    //=============================================================================================
}