<?php

declare(strict_types = 1);

namespace Aether\Cache\Driver;

use Aether\Cache\CacheDriverInterface;
use Config\Cache as CacheConfig;

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
        $this->config = $config;
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
        $setLockKey  = $this->config->savePath . "/" . $this->buildKey($this->nocache);
        $getLockKey  = $this->config->savePath . "/lock_" . $this->buildKey($key);
        $currentTime = time();

        // check if exist and get filemtime of 
        // set lock key
        if (file_exists($setLockKey))
        {
            $createdTime = filemtime($setLockKey);
            $expiredTime = $createdTime + $this->config->lockTTL;
            
            if ($expiredTime < $currentTime)
            {
                // delete
                unlink($setLockKey);
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
        $noCachePath = $this->config->savePath . "/" . $this->buildKey($this->nocache);

        // check if cache on
        if (!$this->config->useCache || file_exists($noCachePath))
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
        $noCachePath = $this->config->savePath . '/' . $this->buildKey($this->nocache);

        // set nocache
        file_put_contents($noCachePath, 1);

        // glob and clear
        $pathPattern = $this->config->savePath . '/' . $this->buildKey('*');

        // unlink if not empty
        $this->removeOnPattern($pathPattern);

        // remove no cache
        unlink($noCachePath);
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
        $noCachePath = $this->config->savePath . "/" . $this->buildKey($this->nocache);

        // cannot get any cache if no cache exist
        if (file_exists($noCachePath))
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

        // return
        return is_string($value) ? json_decode($value, true) : $value;
    }

    //=========================================================================================

    public function set(string $key, mixed $value): bool
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
        $noCachePath = $this->config->savePath . "/" . $this->buildKey($this->nocache);

        // Cannot set any cache if the nocache file exist
        if (file_exists($noCachePath))
        {
            return false;
        }

        // check value
        if (!is_string($value) && !is_null($value) && !is_int($value) && !is_float($value) && !is_double($value))
        {
            $value = json_encode($value);
        }

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