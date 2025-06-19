<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Interface\Config\CacheInterface;

/** 
 * Base Cache Configurations
 * 
 * @class Aether\Config
 * 
**/

class BaseCache extends BaseConfig implements CacheInterface
{
    public bool $useCache = false;
    public string $driver = 'file';
    public string $prefix = '';
    public string $savePath = '';
    public int $lockTTL = 150;

    //==================================================================================

    public function __construct(array|null $config = null)
    {
        $this->useCache = getDotEnv('Cache.useCache', 'bool', $this->useCache);
        $this->driver = getDotEnv('Cache.driver', 'string', $this->driver);
        $this->prefix = getDotEnv('Cache.prefix', 'string', $this->prefix);
        $this->savePath = getDotEnv('Cache.savePath', 'string', $this->savePath);
        $this->lockTTL = getDotEnv('Cache.lockTTL', 'int', $this->lockTTL);

        if (!is_null($config))
        {
            $this->rewriteConfig($config);
        }
    }

    //==================================================================================
}