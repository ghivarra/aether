<?php 

declare(strict_types = 1);

namespace Aether;

use Aether\Session\CustomHandlerInterface;
use Config\Session as SessionConfig;
use Config\Cookie as CookieConfig;
use Aether\Exception\SystemException;

/** 
 * Session Class
 * 
 * @class Aether\Route
**/

class Session
{
    public static CustomHandlerInterface $sessionHandler;
    public static string $purgatory = '__purgatory';
    public static string $tempDataParam = '__tempData';
    public static string $flashDataParam = '__flashData';
    public static string $lastRegenParam = '__last_regenerate';

    //=============================================================================================

    public static function checkSessionStatus(): void
    {
        // check status
        $status = session_status();

        // if session not running yet
        if ($status === PHP_SESSION_NONE)
        {
            $message = (AETHER_ENV === 'development') ? 'Session should be started before using session flash data' : 'Failed to set flash data';
            throw new SystemException($message, 500);
        }
    }

    //=============================================================================================

    public static function clearPurgatory(): bool
    {
        if (!isset($_SESSION[self::$purgatory]))
        {
            // purgatory empty
            return false;
        }

        $keys = $_SESSION[self::$purgatory];

        // remove all if not empty
        if (!empty($keys))
        {
            foreach ($keys as $key):

                if (isset($_SESSION[$key]))
                {
                    unset($_SESSION[$key]);
                }

            endforeach;
        }

        // unset purgatory
        unset($_SESSION[self::$purgatory]);

        // done clear
        return true;
    }

    //=============================================================================================

    public static function purge(string|array $keys): void
    {
        if (!isset($_SESSION[self::$purgatory]))
        {
            $_SESSION[self::$purgatory] = [];
        }

        $purgatory = $_SESSION[self::$purgatory];

        if (is_array($keys))
        {
            foreach ($keys as $key):

                array_push($purgatory, $key);

            endforeach;

        } else {

            array_push($purgatory, $keys);
        }

        // push into purgatory
        $_SESSION[self::$purgatory] = $purgatory;
    }

    //=============================================================================================

    public static function start(): void
    {
        $status = session_status();

        if ($status === PHP_SESSION_NONE)
        {
            $config = new SessionConfig();
            $cookie = new CookieConfig();
    
            // set php.ini config about session
            ini_set('session.gc_maxlifetime', $config->gcLifetime);
            ini_set('session.gc_probability', $config->gcProbability);
            ini_set('session.gc_divisor', $config->gcDivisor);
    
            // set params
            session_set_cookie_params([
                'lifetime' => $config->expiration,
                'domain'   => $cookie->domain,
                'path'     => $cookie->path,
                'secure'   => $cookie->secure,
                'httponly' => $cookie->httponly,
                'samesite' => $cookie->samesite,
            ]);

            // check handler
            self::$sessionHandler = new $config->handlers[$config->handler]();
    
            // set session driver
            session_set_save_handler(self::$sessionHandler);
    
            // start session
            session_start();

            // check if it is time to regenerate session
            $sessCreatedTime = $_SESSION[self::$lastRegenParam];
            $sessExpiredTime = $sessCreatedTime + $config->timeToUpdate;
            $currentTime     = time();

            if ($sessExpiredTime < $currentTime)
            {
                // regenerate id
                session_regenerate_id();

                // set session last regenerate to now
                $_SESSION[self::$lastRegenParam] = time();
            }

            // clear purgatory
            self::clearPurgatory();

            // check and delete flashData
            if (isset($_SESSION[self::$flashDataParam]))
            {
                $sessionKeys = $_SESSION[self::$flashDataParam];

                if (is_array($sessionKeys))
                {
                    // move all keys into purgatory
                    foreach ($sessionKeys as $key):

                        self::purge($key);

                    endforeach;
                }

                // remove flash data key to remove
                unset($_SESSION[self::$flashDataParam]);
            }

            // check and delete tempdata
            if (isset($_SESSION[self::$tempDataParam]))
            {
                $tempDataKey = $_SESSION[self::$tempDataParam];

                if (is_array($tempDataKey))
                {
                    foreach ($tempDataKey as $i => $item):

                        if ($item['expire_at'] < $currentTime)
                        {
                            unset($_SESSION[$item['key']]);
                            unset($tempDataKey[$i]);
                        }

                    endforeach;
                }

                if (empty($tempDataKey))
                {
                    // remove temp data key if empty
                    unset($_SESSION[self::$tempDataParam]);

                } else {

                    // reset sortable key
                    $_SESSION[self::$tempDataParam] = array_merge($tempDataKey);
                }
            }
        }
    }

    //=============================================================================================

    public static function flashData(string|array $data, string $value = ''): void
    {
        // run before
        self::checkSessionStatus();

        // set storage
        $flashKeys = [];

        if (isset($_SESSION[self::$flashDataParam]))
        {
            $flashKeys = $_SESSION[self::$flashDataParam];
        }

        // set flash data
        if (is_array($data))
        {
            foreach ($data as $key => $value):

                $_SESSION[$key] = $value;
                array_push($flashKeys, $key);

            endforeach;

        } else {

            $_SESSION[$data] = $value;
            array_push($flashKeys, $data);
        }

        // put keys again
        $_SESSION[self::$flashDataParam] = $flashKeys;
    }

    //=============================================================================================

    public static function tempData(string|array $data, string $value = '', int $time = 0): void
    {
        // run before
        self::checkSessionStatus();

        // set storage
        $tempKeys = [];

        if (isset($_SESSION[self::$tempDataParam]))
        {
            $tempKeys = $_SESSION[self::$tempDataParam];
        }

        // set temporary data
        if (is_array($data))
        {
            foreach ($data as $item):

                // set variable
                $key   = $item['key'];
                $value = $item['value'];
                $time  = time() + intval($item['time']);

                // set session
                $_SESSION[$key] = $value;

                // push data
                array_push($tempKeys, [
                    'key'       => $key,
                    'expire_at' => $time,
                ]);

            endforeach;

        } else {

            // set variable
            $time = time() + intval($time);

            // set session
            $_SESSION[$data] = $value;
            
            // push data
            array_push($tempKeys, [
                'key'       => $data,
                'expire_at' => $time,
            ]);
        }

        // put keys again
        $_SESSION[self::$tempDataParam] = $tempKeys;
    }

    //=============================================================================================
}