<?php 

declare(strict_types = 1);

namespace Aether\Exception;

use \Exception;
use Aether\Exception\BaseException;

class PageNotFoundException extends BaseException
{
    public function __construct(string $message)
    {
        throw new Exception($message, 404);
    }

    //==========================================================================
}