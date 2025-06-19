<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Interface\Config\SessionInterface;
use Aether\Session\Handler\DatabaseHandler;
use Aether\Session\Handler\FileHandler;
use Aether\Session\Handler\RedisHandler;

/** 
 * Base Session Configurations
 * 
 * @class Aether\Config
 * 
**/

class BaseSession extends BaseConfig implements SessionInterface
{
    public array $handlers = [
        'database' => DatabaseHandler::class,
        'file'     => FileHandler::class,
        'redis'    => RedisHandler::class,
    ];

    //==================================================================================

    public string $handler = 'file';
    public string $cookieName = '';
    public int $expiration = 0;
    public string $savePath = '';
    public int $gcProbability = 0;
    public int $gcLifetime = 0;
    public int $gcDivisor = 0;
    public int $timeToUpdate = 0;
    public bool $useEncryption = false;
    public string $encryptionKey = '';
    public int $redisLockTimeout = 10;

    //==================================================================================

    public function __construct(array|null $config = null)
    {
        // set handler
        $this->handler = getDotEnv('Session.handler', 'string', $this->handler);
        $this->cookieName = getDotEnv('Session.cookieName', 'string', $this->cookieName);
        $this->expiration = getDotEnv('Session.expiration', 'int', $this->expiration);
        $this->savePath = getDotEnv('Session.savePath', 'string', $this->savePath);
        $this->gcProbability = getDotEnv('Session.gcProbability', 'int', $this->gcProbability);
        $this->gcLifetime = getDotEnv('Session.gcLifetime', 'int', $this->gcLifetime);
        $this->gcDivisor = getDotEnv('Session.gcDivisor', 'int', $this->gcDivisor);
        $this->timeToUpdate = getDotEnv('Session.timeToUpdate', 'int', $this->timeToUpdate);
        $this->useEncryption = getDotEnv('Session.useEncryption', 'bool', $this->useEncryption);
        $this->encryptionKey = getDotEnv('Session.encryptionKey', 'string', $this->encryptionKey);
        $this->redisLockTimeout = getDotEnv('Session.redisLockTimeout', 'int', $this->redisLockTimeout);

        if (!is_null($config))
        {
            $this->rewriteConfig($config);
        }
    }

    //==================================================================================
}