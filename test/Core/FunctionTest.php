<?php

declare(strict_types = 1);

namespace Test\Core;

use PHPUnit\Framework\TestCase;
use Config\App;
use Config\Security;
use Config\Services;

final class FunctionTest extends TestCase
{
    protected App $appConfig;
    protected Security $securityConfig;

    //==============================================================================

    private function startup(): void
    {
        // set config
        $this->appConfig      = Services::appConfig();
        $this->securityConfig = Services::securityConfig();
    }

    //==============================================================================

    public function testCSRFToken(): void
    {
        $this->startup();
        
        // use function
        $token = csrfToken();

        // assertion
        $this->assertIsString($token, 'csrfToken() return value should be a string');
        $this->assertSame($this->securityConfig->tokenName, $token, 'csrfToken() return value should be the same value as in config/Security::tokenName');
    }

    //==============================================================================
}