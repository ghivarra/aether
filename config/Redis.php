<?php

declare(strict_types = 1);

namespace Config;

use Aether\Config\BaseRedis;

class Redis extends BaseRedis
{
    /**
     * --------------------------------------------------------------------------
     * Redis URL Scheme 
     * --------------------------------------------------------------------------
     *
     * The connection scheme that will be used to connect to the Redis instance
     * You can also put the 'unix' scheme and set the redis.sock path into $path variable
     * In the 'tls' scheme you should set the public $certPath
     * 
     * @var 'tcp'|'unix'|'tls' $scheme
     */
    public string $scheme = 'tcp';

    /**
     * --------------------------------------------------------------------------
     * Redis Path
     * --------------------------------------------------------------------------
     *
     * Redis connection instance path. with 'unix' scheme, the path should be redis.sock 
     * file path
     *
     * @var string $path
     */
    public string $path = '127.0.0.1';

    /**
     * --------------------------------------------------------------------------
     * Redis Port
     * --------------------------------------------------------------------------
     *
     * Redis connection port socket. with 'unix' scheme, the port config 
     * will not be used
     *
     * @var int $port
     */
    public int $port = 6379;

    /**
     * --------------------------------------------------------------------------
     * Redis Certification Path
     * --------------------------------------------------------------------------
     *
     * Redis public SSL certificate path. with 'tls' scheme and use ssl on this
     * cert will be used on the connection
     *
     * @var string $certPath
     */
    public string $certPath = '';

    /**
     * --------------------------------------------------------------------------
     * Redis Prefix
     * --------------------------------------------------------------------------
     *
     * Redis key prefix that should be used on every commands
     *
     * @var string $prefix
     */
    public string $prefix = 'aether:';
}
