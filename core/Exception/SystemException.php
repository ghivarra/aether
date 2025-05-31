<?php 

declare(strict_types = 1);

namespace Aether\Exception;

use \Exception;

class SystemException
{
    public function __construct(string $message, int|string $code)
    {
        throw new Exception($message, intval($code));
    }

    //==========================================================================
}