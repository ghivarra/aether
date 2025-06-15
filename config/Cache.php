<?php 

declare(strict_types = 1);

namespace Config;

use Aether\Config\BaseCache;


class Cache extends BaseCache
{
    /**
     * --------------------------------------------------------------------------
     * Cache Usage
     * --------------------------------------------------------------------------
     *
     * @var bool $useCache
     */
    public bool $useCache = false;

    /**
     * --------------------------------------------------------------------------
     * Cache Driver
     * --------------------------------------------------------------------------
     *
     * The session storage driver to use:
     * 
     * - 'file' => Using File as a main cache handler storage
     * - 'redis' => using Redis as a main cache handler storage
     *
     * @var string $driver
     */
    public string $driver = 'file';

    /**
     * --------------------------------------------------------------------------
     * Cache Prefix
     * --------------------------------------------------------------------------
     *
     * The cache prefix to avoid collision with another app
     */
    public string $prefix = 'ae_cache';

    /**
     * --------------------------------------------------------------------------
     * Cache Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save caches to and is driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     * 
     * For the 'redis' driver, it's a prefix before the cache prefix
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     */
    public string $savePath = STORAGEPATH . 'cache';

    /**
     * --------------------------------------------------------------------------
     * No Cache File TTL
     * --------------------------------------------------------------------------
     *
     * In 'file' handler, When there are a problem in clearing the cache/lock and the NOCACHE/lock 
     * file failed to be removed, this is the max lifetime of the NOCACHE/lock file.
     * 
     * IN SECONDS
     */
    public int $lockTTL = 150;
}