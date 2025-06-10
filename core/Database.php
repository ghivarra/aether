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

    // [ 'query' => '', 'time' => 0 ]
    protected static array $storedQueries = [];

    //==================================================================================================

    /** 
     * Current connection storage, so we don't have to connect
     * or reconnect again
     * 
     * @var array self::$currentConnection
    **/
    public static array $currentConnection = [];
    
    //==================================================================================================

    public static function getCurrentConnection(string|null $defaultConnection = null): DriverInterface|array|null
    {
        // if null then return all
        if (is_null($defaultConnection))
        {
            return self::$currentConnection;
        }

        // default
        $conn = $defaultConnection;

        // return
        return isset(self::$currentConnection[$conn]) ? self::$currentConnection[$conn] : null;
    }

    //==================================================================================================

    public static function connect(string $defaultConnection = ''): DriverInterface
    {
        // initiate config
        $config = new DatabaseConfig();

        // set based on config
        $defaultConnection = ($defaultConnection === '') ? $config->defaultDB : $defaultConnection;
        
        // check
        if (!isset(self::$currentConnection[$defaultConnection]))
        {
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
            $conn = $DB->connect($realConfig, $defaultConnection);

            // push connection
            self::$currentConnection[$defaultConnection] = $conn;
        }

        // return
        return self::$currentConnection[$defaultConnection];
    }

    //==================================================================================================

    public static function disconnect(string $defaultConnection = 'default'): bool
    {
        if (!isset(self::$currentConnection[$defaultConnection]))
        {
            return false;
        }

        // disconnect
        self::$currentConnection[$defaultConnection]->disconnect();

        // remove instance
        unset(self::$currentConnection[$defaultConnection]);

        // return
        return true;
    }

    //==================================================================================================

    public static function getAllQueries(): array
    {
        return self::$storedQueries;
    }

    //==================================================================================================

    public static function storeQuery(string $query, float|int $time, array $backtrace = []): void
    {
        array_push(self::$storedQueries, [
            'query'     => $query,
            'time'      => $time,
            'backtrace' => $backtrace,
        ]);
    }

    //==================================================================================================
}