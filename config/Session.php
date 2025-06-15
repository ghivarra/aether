<?php 

declare(strict_types = 1);

namespace Config;

use Aether\Config\BaseSession;

class Session extends BaseSession
{
    /**
     * --------------------------------------------------------------------------
     * Session Driver
     * --------------------------------------------------------------------------
     *
     * The session storage driver to use:
     * 
     * - 'database' => Using Database as a main session handler storage
     * - 'file' => Using File as a main session handler storage
     * - 'redis' => using Redis as a main session handler storage
     *
     * @var string $handler
     */
    public string $handler = 'file';

    /**
     * --------------------------------------------------------------------------
     * Session Cookie Name
     * --------------------------------------------------------------------------
     *
     * The session cookie name, must contain only [0-9a-z_-] characters
     */
    public string $cookieName = 'ae_session';

    /**
     * --------------------------------------------------------------------------
     * Session Expiration
     * --------------------------------------------------------------------------
     *
     * The number of SECONDS you want the session to last.
     * Setting to 0 (zero) means expire when the browser is closed.
     */
    public int $expiration = 7200;

    /**
     * --------------------------------------------------------------------------
     * Session GC Lifetime
     * --------------------------------------------------------------------------
     *
     * The time the session will persist on the storage before deleted by garbage collector
     */
    public int $gcLifetime = 360;

    /**
     * --------------------------------------------------------------------------
     * Session GC Probability
     * --------------------------------------------------------------------------
     *
     * The number of probability that will be divided by gcDivisor to run Session Garbage Collector
     * i.e. if the probability is 10 and the divisor is 100 then it will be 10/100 probability that
     * Garbage Collector will run 
     */
    public int $gcProbability = 5;

    /**
     * --------------------------------------------------------------------------
     * Session GC Divisor
     * --------------------------------------------------------------------------
     *
     * The number of divisor that will divide the gcProbability to run Session Garbage Collector
     * i.e. if the probability is 10 and the divisor is 100 then it will be 10/100 probability that
     * Garbage Collector will run 
     */
    public int $gcDivisor = 100;

    /**
     * --------------------------------------------------------------------------
     * Session Save Path
     * --------------------------------------------------------------------------
     *
     * The location to save sessions to and is driver dependent.
     *
     * For the 'files' driver, it's a path to a writable directory.
     * WARNING: Only absolute paths are supported!
     *
     * For the 'database' driver, it's a table name.
     * Please read up the manual for the format with other session drivers.
     * 
     * For the 'redis' driver, it's a session prefix before the session name
     *
     * IMPORTANT: You are REQUIRED to set a valid save path!
     */
    public string $savePath = STORAGEPATH . 'session';

    /**
     * --------------------------------------------------------------------------
     * Session Time to Update
     * --------------------------------------------------------------------------
     *
     * How many seconds between CI regenerating the session ID.
     */
    public int $timeToUpdate = 10;

    /**
     * --------------------------------------------------------------------------
     * Session Encryption
     * --------------------------------------------------------------------------
     *
     * Whether to encrypt to stored session data.
     */
    public bool $useEncryption = false;

    /**
     * --------------------------------------------------------------------------
     * Session Encryption Key
     * --------------------------------------------------------------------------
     *
     * 256-bit key for encryption. (usually 32 characters count)
     */
    public string $encryptionKey = '';

    /**
     * --------------------------------------------------------------------------
     * Redis Lock Timeout
     * --------------------------------------------------------------------------
     *
     * How many seconds to wait while the incoming request wanted to handle the session, 
     * this is useful if a script end abruptly or throwing error and failed to clear the lock
     * the next incoming request will always retries to set the sesion lock for every 100ms 
     * even while the lock still persist
     */
    public int $redisLockTimeout = 60;
}