<?php

declare(strict_types = 1);

namespace Aether\Session;

use \SessionHandlerInterface;
use \SessionIdInterface;

/** 
 * Custom Handler Interface
 * 
 * @class Aether\Route
**/

interface CustomHandlerInterface extends SessionHandlerInterface, SessionIdInterface
{
    // inherit all from session handler interface and session id interface
}