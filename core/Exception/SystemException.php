<?php 

declare(strict_types = 1);

namespace Aether\Exception;

use \Exception;
use Aether\Exception\BaseException;

class SystemException extends BaseException
{
    public function __construct(string $message, int|string $code)
    {
        throw new Exception($message, intval($code));
    }

    //==========================================================================
}