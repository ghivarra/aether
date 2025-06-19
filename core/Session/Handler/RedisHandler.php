<?php 

declare(strict_types = 1);

namespace Aether\Session\Handler;

use Aether\Session\CustomHandlerInterface;
use Aether\Session\Handler\SessionHandlerTrait;
use Aether\Redis;
use Config\Session as SessionConfig;
use Predis\Client as RedisClient;
use Aether\Exception\SystemException;
use Config\Services;

class RedisHandler implements CustomHandlerInterface
{
    private $divider = ':';
    private SessionConfig $config;
    private RedisClient $redis;
    private int $ttl = 0;
    private string $sessionPrefix = '';
    private string $sessionID = '';
    private string|false $lockKey = false;

    //========================================================================================================

    use SessionHandlerTrait;

    //========================================================================================================

    public function __construct(RedisClient|null $client = null)
    {
        $this->redis  = is_null($client) ? Redis::connect() : $client;
        $this->config = Services::sessionConfig();
    }

    //========================================================================================================

    public function close(): bool
    {
        // check if lock still exist
        // and release the key
        if ($this->lockKey)
        {
            $this->redis->del($this->lockKey);
            $this->lockKey = false;
        }

        // return true
        return true;
    }

    //========================================================================================================

    public function create_sid(): string
    {
        return $this->sessionPrefix . $this->divider . $this->config->cookieName . $this->divider . bin2hex(random_bytes(16)) . $this->divider . dechex(time());
    }

    //========================================================================================================

    public function destroy(string $sessionID): bool
    {
        $this->redis->del($sessionID);
        
        // return
        return true;
    }

    //========================================================================================================

    public function gc(int $maxLifetime): int|false
    {
        // automatic GC on redis
        return 0;
    }

    //========================================================================================================

    public function open(string $sessionPrefix, string $sessionName): bool
    {
        // set session prefix
        $this->sessionPrefix = $this->config->savePath;

        // automatically connect to redis on __construct
        return true;
    }

    //========================================================================================================

    public function read(string $sessionID): string|false
    {
        $this->sessionID = $sessionID;
        $this->lockKey   = "lock{$this->divider}{$sessionID}";

        // variable if lock is get and how many tries
        $sessionLocked = false;
        $maxRetries    = 1200;

        while (!$sessionLocked && $maxRetries-- > $maxRetries)
        {
            $redisLockSet  = $this->redis->set($this->lockKey, 1, 'EX', $this->config->redisLockTimeout, 'NX');
            $sessionLocked = is_null($redisLockSet) ? false : true;

            if (!$sessionLocked)
            {
                // if not acquired wait for 100 ms
                // before retrying to put the lock again
                usleep(100_000);
            }
        }

        if (!$sessionLocked)
        {
            // failed to lock the session and return empty
            $message = (AETHER_ENV === 'development') ? "Failed to set the lock key on session {$sessionID}. Check the redis connection" : "Failed to fetch session data";
            throw new SystemException($message, 500);
        }

        // data
        $data = $this->redis->get($sessionID);

        // if data is null / not set yet
        if (is_null($data))
        {
            // initial data
            $time        = time();
            $initialData = "__last_regenerate|i:{$time};";
            $initialData = ($this->config->useEncryption) ? $this->encryptSessionData($initialData) : $initialData;

            // create new
            $this->redis->setex($sessionID, $this->config->expiration, $initialData);

            // return
            return $initialData;
        }
        
        // return data
        return ($this->config->useEncryption) ? $this->decryptSessionData($data) : $data;
    }

    //========================================================================================================

    public function write(string $sessionID, string $data): bool
    {
        // set if should encrypted or not
        $data = ($this->config->useEncryption) ? $this->encryptSessionData($data) : $data;

        // write data
        $set = $this->redis->setex($sessionID, $this->config->expiration, $data);
        
        // return true if status 
        return is_null($set) ? false : true;
    }

    //========================================================================================================
}
