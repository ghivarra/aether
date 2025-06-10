<?php

declare(strict_types = 1);

namespace Aether;

use Config\Redis as RedisConfig;
use Predis\Client as RedisClient;
use Aether\Exception\SystemException;
use Aether\Redis\CustomConnection;

class Redis
{
    private static RedisClient|null $currentConnection = null;

    //=============================================================================================

    private static function unixConnect(RedisConfig $config): RedisClient
    {
        $connection = new RedisClient([
            'scheme' => $config->scheme,
            'path'   => $config->path,
        ], [
            'prefix' => $config->prefix,
        ]);

        // connect
        $connection->connect();
        
        // return
        return $connection;
    }

    //=============================================================================================

    private static function tcpConnect(RedisConfig $config): RedisClient
    {
        $connection = new RedisClient([
            'scheme' => $config->scheme,
            'path'   => $config->path,
            'port'   => $config->port,
        ], [
            'prefix'      => $config->prefix,
            'connections' => [
                'tcp' => CustomConnection::class
            ]
        ]);

        // connect
        $connection->connect();
        
        // return
        return $connection;
    }

    //=============================================================================================

    private static function tlsConnect(RedisConfig $config): RedisClient
    {
        $connection = new RedisClient([
            'scheme' => $config->scheme,
            'path'   => $config->path,
            'port'   => $config->port,
            'prefix' => $config->prefix,
            'ssl'    => [
                'cafile'      => $config->certPath,
                'verify_peer' => true,
            ],
        ], [
            'prefix' => $config->prefix,
        ]);

        // connect
        $connection->connect();
        
        // return
        return $connection;
    }

    //=============================================================================================

    public static function getCurrentConnection(): RedisClient|null
    {
        return self::$currentConnection;
    }

    //=============================================================================================

    public static function connect(RedisConfig|null $config = null)
    {
        $config = is_null($config) ? new RedisConfig() : $config;

        // check if connection does not exist
        if (is_null(self::$currentConnection))
        {
            switch ($config->scheme) {
                case 'tls':
                    self::$currentConnection = self::tlsConnect($config);
                    break;

                case 'unix':
                    self::$currentConnection = self::unixConnect($config);
                    break;
                
                default:
                    // the default scheme is tcp
                    self::$currentConnection = self::tcpConnect($config);
                    break;
            }

            if (!self::$currentConnection->isConnected())
            {
                throw new SystemException('Cannot connect to Redis.', 500);
            }
        }

        // return connection
        return self::$currentConnection;
    }

    //=============================================================================================

    public static function disconnect(): bool
    {
        if (is_null(self::$currentConnection))
        {
            return false;
        }

        if (!self::$currentConnection->isConnected())
        {
            return false;
        }
        
        self::$currentConnection->disconnect();
        self::$currentConnection = null;

        return true;
    }    

    //=============================================================================================
}