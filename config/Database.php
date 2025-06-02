<?php namespace Config;

use Aether\Config\BaseDatabase;

class Database extends BaseDatabase
{
    /**
     * Lets you choose which connection group to use if no other is specified.
     * 
     * @var string $defaultDB
     */
    public string $defaultDB = 'default';

    /**
     * Default DB configurations used, you can create multiple instance with
     * different name, like in the commented example
     * 
     * Don't delete or commenting this default config even if not used
     * 
     * The opion for supported DBDriver so far is MySQLi and PostgreSQL
     * 
     * @var array $default
     */
    public array $default = [
        'hostname' => 'localhost',
        'port'     => 3306,
        'username' => 'ghivarra',
        'password' => '',
        'database' => 'aether',
        'DBDriver' => 'MySQLi',
        'DBPrefix' => '',
        'DBDebug'  => (AETHER_ENV === 'development'),
        'charset'  => 'utf8mb4',
        'DBCollat' => 'utf8mb4_general_ci',
    ];

//    public array $secondary = [
//        'hostname' => 'localhost',
//        'port'     => 5432,
//        'username' => '',
//        'password' => '',
//        'database' => '',
//        'DBDriver' => 'PostgreSQL',
//        'DBPrefix' => '',
//        'DBDebug'  => (AETHER_ENV === 'development'),
//        'charset'  => 'UTF8',
//        'DBCollat' => 'UTF8',
//    ];
}