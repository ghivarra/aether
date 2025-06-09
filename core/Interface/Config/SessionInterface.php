<?php namespace Aether\Interface\Config;

/** 
 * Base Session Interface
 * 
 * @class Aether\Interface\Config
 * 
**/

interface SessionInterface
{
    public string $handler {get; set;}
    public string $cookieName {get; set;}
    public int $expiration {get; set;}
    public string $savePath {get; set;}
    public int $gcProbability {get; set;}
    public int $timeToUpdate {get; set;}
    public bool $useEncryption {get; set;}
    public string $encryptionKey {get; set;}
}