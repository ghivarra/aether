<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Interface\Config\RedisInterface;

/** 
 * Base Redis Configurations
 * 
 * @class Aether\Config
 * 
**/

class BaseRedis extends BaseConfig implements RedisInterface
{
    public string $scheme = '';
    public string $path = '';
    public int $port = 6379;
    public string $certPath = '';
    public string $prefix = '';

    //==================================================================================

    public function __construct(array|null $config = null)
    {
        $this->scheme = getDotEnv('Redis.scheme', 'string', $this->scheme);
        $this->path = getDotEnv('Redis.path', 'string', $this->path);
        $this->port = getDotEnv('Redis.port', 'int', $this->port);
        $this->certPath = getDotEnv('Redis.certPath', 'string', $this->certPath);
        $this->prefix = getDotEnv('Redis.prefix', 'string', $this->prefix);

        if (!is_null($config))
        {
            $this->rewriteConfig($config);
        }
    }

    //==================================================================================
}