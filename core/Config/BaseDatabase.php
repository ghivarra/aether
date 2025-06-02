<?php

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Interface\Config\DatabaseInterface;

/** 
 * Base Database Configurations
 * 
 * @class Aether\Config
 * 
**/

class BaseDatabase implements DatabaseInterface
{
    public function __construct()
    {
        // set default DB option
        $this->defaultDB = getDotEnv('Database.defaultDB', 'string', $this->defaultDB);

        // set all env on another DB option
        foreach ($this as $variable => $item):

            if ($variable !== 'defaultDB' && is_array($item) && isset($item['hostname']))
            {
                $this->$variable['hostname'] = getDotEnv("Database.{$variable}.hostname", 'string', $item['hostname']);
                $this->$variable['port'] = getDotEnv("Database.{$variable}.port", 'int', $item['port']);
                $this->$variable['username'] = getDotEnv("Database.{$variable}.username", 'string', $item['username']);
                $this->$variable['password'] = getDotEnv("Database.{$variable}.password", 'string', $item['password']);
                $this->$variable['database'] = getDotEnv("Database.{$variable}.database", 'string', $item['database']);
                $this->$variable['DBDriver'] = getDotEnv("Database.{$variable}.DBDriver", 'string', $item['DBDriver']);
                $this->$variable['DBPrefix'] = getDotEnv("Database.{$variable}.DBPrefix", 'string', $item['DBPrefix']);
                $this->$variable['DBDebug'] = getDotEnv("Database.{$variable}.DBDebug", 'bool', $item['DBDebug']);
                $this->$variable['charset'] = getDotEnv("Database.{$variable}.charset", 'string', $item['charset']);
                $this->$variable['DBCollat'] = getDotEnv("Database.{$variable}.DBCollat", 'string', $item['DBCollat']);
            }

        endforeach;
    }

    //==================================================================================

    public string $defaultDB = '';

    //==================================================================================

    public array $default = [
        'hostname' => '',
        'port'     => 3306,
        'username' => '',
        'password' => '',
        'database' => '',
        'DBDriver' => '',
        'DBPrefix' => '',
        'DBDebug'  => false,
        'charset'  => '',
        'DBCollat' => '',
    ];

    //==================================================================================
}