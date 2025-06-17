<?php

declare(strict_types = 1);

namespace Test\Core;

use PHPUnit\Framework\TestCase;
use Config\App as AppConfig;
use Config\Security as SecurityConfig;

final class FunctionTest extends TestCase
{
    protected AppConfig $appConfig;
    protected SecurityConfig $securityConfig;

    //==============================================================================

    private function startup(): void
    {
        // set config
        $this->appConfig = new AppConfig();
        $this->securityConfig = new SecurityConfig();
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