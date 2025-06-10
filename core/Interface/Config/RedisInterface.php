<?php namespace Aether\Interface\Config;

interface RedisInterface
{
    public string $scheme { get; set; }
    public string $path { get; set; }
    public int $port { get; set; }
    public string $certPath { get; set; }
    public string $prefix { get; set; }
}