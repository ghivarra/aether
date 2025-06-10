<?php 

declare(strict_types = 1);

namespace Aether\Exception;

class BaseException
{
    public function __construct()
    {
        // always run on throw error

        // close the session to manually remove the lock on
        // throw error
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            session_write_close();
        }
    }

    //==========================================================================
}