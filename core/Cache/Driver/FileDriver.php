<?php

declare(strict_types = 1);

namespace Aether\Cache\Driver;

use Aether\Cache\CacheDriverInterface;
use Config\Cache as CacheConfig;
use \Throwable;

/** 
 * File Driver for Cache Library
 * 
 * @class Aether\Cache\Driver
 * 
**/

class FileDriver implements CacheDriverInterface
{
    protected string $divider = '_';
    protected string $nocache = 'NOCACHE.tmp';
    protected CacheConfig $config;

    //=========================================================================================

    public function __construct(CacheConfig $config)
    {
        $this->config  = $config;
        
        $savePath      = url_title($this->config->savePath, '-', true);
        $this->nocache = $this->config->savePath . "/{$savePath}{$this->divider}{$this->nocache}";
    }

    //=========================================================================================

    protected function removeOnPattern(string $pattern): void
    {
        if (PHP_OS_FAMILY === 'Linux' || PHP_OS_FAMILY === 'Darwin')
        {
            if (function_exists('exec'))
            {
                exec('rm -f ' . $pattern);

                // early return
                return;
            }
        }

        foreach (glob($pattern) as $file):

            // delete file
            unlink($file);

        endforeach;
    }

    //=========================================================================================

    protected function checkLockTTL(string $key): void
    {
        $getLockKey  = $this->config->savePath . "/lock_" . $this->buildKey($key);
        $currentTime = time();

        // check if exist and get filemtime of 
        // set lock key
        if (file_exists($this->nocache))
        {
            $createdTime = filemtime($this->nocache);
            $expiredTime = $createdTime + $this->config->lockTTL;
            
            if ($expiredTime < $currentTime)
            {
                // delete
                unlink($this->nocache);
            }
        }

        // check if exist and get filemtime of 
        // get lock key
        if (file_exists($getLockKey))
        {
            $createdTime = filemtime($getLockKey);
            $expiredTime = $createdTime + $this->config->lockTTL;
            
            if ($expiredTime < $currentTime)
            {
                // delete
                unlink($getLockKey);
            }
        }
    }

    //=========================================================================================

    public function buildKey(string $originalKey): string
    {
        return "{$this->config->prefix}{$this->divider}{$originalKey}";
    }

    //=========================================================================================

    public function check(): bool
    {
        // check if cache on
        if (!$this->config->useCache || file_exists($this->nocache))
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
        file_put_contents($this->nocache, 1);

        // glob and clear
        $pathPattern = $this->config->savePath . '/' . $this->buildKey('*');

        // unlink if not empty
        $this->removeOnPattern($pathPattern);

        // remove no cache
        unlink($this->nocache);
    }

    //=========================================================================================

    public function delete(string $pattern): void
    {
        $pathPattern = $this->config->savePath . '/' . $this->buildKey($pattern);

        if (!str_contains($pathPattern, '*'))
        {
            unlink($pathPattern);

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

        // run gc
        $this->checkLockTTL($key);

        // build full path
        $path        = $this->config->savePath . "/" . $this->buildKey($key);
        $lockPath    = $this->config->savePath . "/" . $this->buildKey('lock_' . $key);

        // cannot get any cache if no cache exist
        if (file_exists($this->nocache))
        {
            return false;
        }

        if (file_exists($lockPath))
        {
            // check value
            $value = file_get_contents($lockPath);

        } else {

            // check
            if (!file_exists($path) || !is_readable($path))
            {
                return false;
            }

            // get value
            $value = file_get_contents($path);
        }

        // decode value
        try {
            
            $value = json_decode($value, true);

        } catch (Throwable $e) {

            // delete file & lock
            // if failed to decode
            if (file_exists($path))
            {
                unlink($path);
            }

            if (file_exists($lockPath))
            {
                unlink($lockPath);
            }

            // return
            return false;
        }
        
        // check TTL
        // delete if already expired
        if (isset($value['expired_at']))
        {
            if ($value['expired_at'] < time())
            {
                // delete file & lock
                if (file_exists($path))
                {
                    unlink($path);
                }

                if (file_exists($lockPath))
                {
                    unlink($lockPath);
                }

                // return
                return false;
            }
        }

        // return
        return $value['data'];
    }

    //=========================================================================================

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        // check if disabled in config
        if (!$this->config->useCache)
        {
            return false;
        }

        // run gc
        $this->checkLockTTL($key);

        // build full path
        $path        = $this->config->savePath . "/" . $this->buildKey($key);
        $lockPath    = $this->config->savePath . "/" . $this->buildKey('lock_' . $key);

        // Cannot set any cache if the nocache file exist
        if (file_exists($this->nocache))
        {
            return false;
        }

        // check value
        $value = [
            'created_at' => time(),
            'data'       => $value,
        ];

        if ($ttl > 0)
        {
            $value['expired_at'] = time() + $ttl;
        }

        // encode value
        $value = json_encode($value);

        // check if file already exist and create lock
        if (file_exists($path))
        {
            $oldData = file_get_contents($path);

            // create set lock
            file_put_contents($lockPath, $oldData);

            // modify data
            file_put_contents($path, $value);

            // remove lock
            unlink($lockPath);

        } else {

            // modify data
            file_put_contents($path, $value);
        }

        // return
        return true;
    }

    //=========================================================================================
}