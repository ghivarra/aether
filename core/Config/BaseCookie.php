<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Interface\Config\CookieInterface;

/** 
 * Base Cookie Configurations
 * 
 * @class Aether\Config
 * 
**/

class BaseCookie implements CookieInterface
{
    public string $prefix = 'file';
    public int $expires = 0;
    public string $path = '';
    public string $domain = '';
    public bool $secure = false;
    public bool $httponly = false;
    public string $samesite = 'Lax';

    //==================================================================================

    public function __construct()
    {
        $this->prefix = getDotEnv('Cookie.prefix', 'string', $this->prefix);
        $this->expires = getDotEnv('Cookie.expires', 'int', $this->expires);
        $this->path = getDotEnv('Cookie.path', 'string', $this->path);
        $this->domain = getDotEnv('Cookie.domain', 'string', $this->domain);
        $this->secure = getDotEnv('Cookie.secure', 'bool', $this->secure);
        $this->httponly = getDotEnv('Cookie.httponly', 'bool', $this->httponly);
        $this->samesite = getDotEnv('Cookie.samesite', 'string', $this->samesite);
    }

    //==================================================================================
}