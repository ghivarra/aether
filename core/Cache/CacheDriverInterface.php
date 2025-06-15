<?php namespace Aether\Cache;

interface CacheDriverInterface
{
    public function buildKey(string $originalKey): string;
    public function check(): bool;
    public function clear(): void;
    public function delete(string $pattern): void;
    public function get(string $key): mixed;
    public function set(string $key, mixed $value): bool;
}