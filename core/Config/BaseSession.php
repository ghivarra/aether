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

class BaseSession implements SessionInterface
{
    public array $drivers = [
        'database' => DatabaseHandler::class,
        'file'     => FileHandler::class,
        'redis'    => RedisHandler::class,
    ];

    //==================================================================================

    public string $handler = 'file';
    public string $cookieName = '';
    public int $expiration = 0;
    public string $savePath = '';
    public int $timeToUpdate = 0;

    //==================================================================================
}