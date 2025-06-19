<?php

declare(strict_types = 1);

namespace Aether;

use Aether\Cache\Driver\FileDriver;
use Aether\Cache\Driver\RedisDriver;
use Aether\Cache\CacheDriverInterface;
use Config\Cache as CacheConfig;
use Config\Services;

/** 
 * The cache class
 * 
 * @class Aether
**/

class Cache implements CacheDriverInterface
{
    protected array $driverList = [
        'file'  => FileDriver::class,
        'redis' => RedisDriver::class,
    ];

    //==================================================================================

    public static CacheDriverInterface $instance;

    //==================================================================================

    public function __construct(CacheConfig|null $config = null)
    {
        $config = is_null($config) ? Services::cacheConfig() : $config;

        // run cache driver based on config
        self::$instance = new $this->driverList[$config->driver]($config);
    }

    //=================================================================================

    public function buildKey(string $originalKey): string
    {
        return self::$instance->buildKey($originalKey);
    }

    //=================================================================================

    public function check(): bool
    {
        return self::$instance->check();
    }

    //=================================================================================

    public function clear(): void
    {
        self::$instance->clear();
    }

    //=================================================================================

    public function delete(string $pattern): void
    {
        self::$instance->delete($pattern);
    }

    //=================================================================================

    public function get(string $key): mixed
    {
        $key = url_title($key, '-', false);
        
        return self::$instance->get($key);
    }

    //=================================================================================

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        $key = url_title($key, '-', false);
        
        return self::$instance->set($key, $value, $ttl);
    }

    //=================================================================================
}