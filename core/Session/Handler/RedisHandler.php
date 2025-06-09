<?php 

declare(strict_types = 1);

namespace Aether\Session\Handler;

use SessionHandlerInterface;
use SessionIdInterface;

class RedisHandler implements SessionHandlerInterface, SessionIdInterface
{
    public function close(): bool
    {
        return true;
    }

    public function create_sid(): string
    {
        return '';
    }

    public function destroy(string $id): bool
    {
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return false;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        return false;
    }

    public function write(string $id, string $data): bool
    {
        return true;
    }
}
