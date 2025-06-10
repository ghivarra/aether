<?php

declare(strict_types = 1);

namespace Aether\Redis;

use Predis\Connection\StreamConnection;

class CustomConnection extends StreamConnection
{
    public function __destruct()
    {
        // forcibly disconnect redis
        $this->disconnect();   
    }
}