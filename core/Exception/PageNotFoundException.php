<?php namespace Aether\Exception;

use \Exception;

class PageNotFoundException
{
    public function __construct(string $message)
    {
        throw new Exception($message, 404);
    }

    //==========================================================================
}