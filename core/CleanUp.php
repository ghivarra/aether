<?php

declare(strict_types = 1);

namespace Aether;

use Aether\Database;
use Aether\Redis;

/** 
 * The clean up class for disconnecting database, redis, session etc
 * if it is not disconnected manually
 * 
 * @class Aether
**/

class CleanUp
{
    public static function trigger(): void
    {
        // check and disconnect session
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            session_write_close();
        }

        // check and disconnect redis if not null
        $redis = Redis::getCurrentConnection();

        if (!is_null($redis))
        {
            Redis::disconnect();
        }

        // check and disconnect db if not null
        $activeConnections = Database::getCurrentConnection();

        if (!empty($activeConnections))
        {
            if (is_array($activeConnections))
            {
                foreach ($activeConnections as $connection):

                    $connection->disconnect();

                endforeach;

            } else {

                $activeConnections->disconnect();
            }
        }
    }
}