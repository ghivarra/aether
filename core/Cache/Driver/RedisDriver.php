<?php

declare(strict_types = 1);

namespace Aether\Cache\Driver;

use Aether\Cache\CacheDriverInterface;
use Config\Cache as CacheConfig;
use Aether\Redis;
use Predis\Collection\Iterator\Keyspace;
use Predis\Client;

/** 
 * Redis Driver for Cache Library
 * 
 * @class Aether\Cache\Driver
 * 
**/

class RedisDriver implements CacheDriverInterface
{
    protected string $divider = ':';
    protected string $nocache = 'NOCACHE';
    protected CacheConfig $config;
    protected Client $redis;

    //=========================================================================================

    public function __construct(CacheConfig $config)
    {
        $this->config = $config;
        $this->redis = Redis::connect();

        // set no cache
        $savePath      = url_title($this->config->savePath);
        $this->nocache = "{$savePath}{$this->divider}{$this->nocache}";
    }

    //=========================================================================================

    protected function removeOnPattern(string $pattern): void
    {
        $batch   = 500;
        $delKeys = [];
        $n       = 0;

        foreach (new Keyspace($this->redis, Redis::buildKey($pattern)) as $key):

            // push to soon to be deleted keys
            array_push($delKeys, Redis::removePrefix($key));
            $n++;

            if ($n >= $batch)
            {
                // delete keys and make the counter zero again
                $this->redis->del($delKeys);
                $delKeys = [];
                $n = 0;
            }

        endforeach;

        // delete remaining key
        // if not empty
        if (!empty($delKeys))
        {
            $this->redis->del($delKeys);
        }
    }

    //=========================================================================================

    public function buildKey(string $originalKey): string
    {
        $savePath = url_title(str_replace(DIRECTORY_SEPARATOR, $this->divider, $this->config->savePath));

        // return
        return "{$savePath}{$this->divider}{$this->config->prefix}{$this->divider}{$originalKey}";
    }

    //=========================================================================================

    public function check(): bool
    {
        // check if cache on
        if (!$this->config->useCache || !is_null($this->redis->get($this->nocache)))
        {
            // return
            return false;
        }

        // return
        return true;
    }

    //=========================================================================================

    public function clear(): void
    {
        // set nocache
        $this->redis->set($this->nocache, 1, 'EX', $this->config->lockTTL, 'NX');

        // glob and clear
        $pathPattern = $this->buildKey('*');

        // unlink if not empty
        $this->removeOnPattern($pathPattern);

        // remove no cache
        $this->redis->del($this->nocache);
    }

    //=========================================================================================

    public function delete(string $pattern): void
    {
        $pathPattern = $this->buildKey($pattern);

        if (!str_contains($pathPattern, '*'))
        {
            $this->redis->del($pathPattern);

            // early return
            return;
        }

        // unlink if not empty
        $this->removeOnPattern($pathPattern);
    }

    //=========================================================================================

    public function get(string $key): mixed
    {
        // check if disabled in config
        if (!$this->config->useCache)
        {
            return false;
        }

        // build full path
        $path     = $this->buildKey($key);
        $lockPath = $this->buildKey('lock_' . $key);

        // cannot get any cache if no cache exist
        if (!is_null($this->redis->get($this->nocache)))
        {
            return false;
        }

        $lockData = $this->redis->get($lockPath);

        if (!is_null($lockData))
        {
            // check value
            $value = $lockData;

        } else {

            // check
            $data = $this->redis->get($path);

            if (is_null($data))
            {
                return false;
            }

            // get value
            $value = $data;
        }

        // return
        return is_string($value) ? json_decode($value, true) : $value;
    }

    //=========================================================================================

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        // check if disabled in config
        if (!$this->config->useCache)
        {
            return false;
        }

        // build full path
        $path     = $this->buildKey($key);
        $lockPath = $this->buildKey('lock_' . $key);

        // cannot set any cache if no cache exist
        if (!is_null($this->redis->get($this->nocache)))
        {
            return false;
        }

        // check value
        if (!is_string($value) && !is_null($value) && !is_int($value) && !is_float($value) && !is_double($value))
        {
            $value = json_encode($value);
        }

        $oldData = $this->redis->get($path);

        // check if file already exist and create lock
        if (!is_null($oldData))
        {
            // create set lock
            $this->redis->set($lockPath, $oldData, 'EX', $this->config->lockTTL, 'NX');

            // modify data
            if ($ttl > 0)
            {
                $this->redis->set($path, $value, 'EX', $ttl, 'NX');

            } else {

                $this->redis->set($path, $value);
            }

            // remove lock
            $this->redis->del($lockPath);

        } else {

            // modify data
            if ($ttl > 0)
            {
                $this->redis->set($path, $value, 'EX', $ttl, 'XX');

            } else {

                $this->redis->set($path, $value);
            }
        }

        // return
        return true;
    }

    //=========================================================================================
}