<?php 

declare(strict_types = 1);

namespace Aether;

use Config\Database as DatabaseConfig;
use Aether\Database\Driver\MySQLi;
use Aether\Database\Driver\PostgreSQL;
use Aether\Exception\SystemException;
use Aether\Database\DriverInterface;

/** 
 * Aether Database
 * 
 * @class Aether\Database
**/

class Database
{
    protected static array $DBDrivers = [
        'MySQLi'     => '\\' . MySQLi::class,
        'PostgreSQL' => '\\' . PostgreSQL::class,
    ];

    //==================================================================================================

    /** 
     * Current connection storage, so we don't have to connect
     * or reconnect again
     * 
     * @var array self::$currentConnection
    **/
    public static array $currentConnection = [];
    
    //==================================================================================================

    public static function connect(string $defaultConnection = 'default'): DriverInterface
    {
        if (!isset(self::$currentConnection[$defaultConnection]))
        {
            // initiate config
            $config = new DatabaseConfig();

            // create fallback message
            $fallbackMessage = 'Failed to connect to the database';

            if (!isset($config->$defaultConnection))
            {
                $message = (AETHER_ENV === 'development') ? "\"{$defaultConnection}\" Database Connection is not found" : $fallbackMessage;

                throw new SystemException($message, 400);
            }

            $realConfig = $config->$defaultConnection;

            // check if DB Driver exist
            if (!isset(self::$DBDrivers[$realConfig['DBDriver']]))
            {
                $message = (AETHER_ENV === 'development') ? "\"{$realConfig['DBDriver']}\" Database Driver is not supported yet" : $fallbackMessage;

                throw new SystemException($message, 400);
            }

            // initiate class
            $DB   = new self::$DBDrivers[$realConfig['DBDriver']]();
            $conn = $DB->connect($realConfig);

            // push connection
            self::$currentConnection[$defaultConnection] = $conn;
        }

        // return
        return self::$currentConnection[$defaultConnection];
    }

    //==================================================================================================
}