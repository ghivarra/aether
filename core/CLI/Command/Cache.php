<?php

declare(strict_types = 1);

namespace Aether\CLI\Command;

use Config\Services;

class Cache
{
    public function clear(): void
    {
        $cache = Services::cache();
        $cache->clear();
    }
}