<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Interface\Config\SecurityInterface;

/** 
 * Base App Configurations
 * 
 * @class Aether\Config
 * 
**/

class BaseSecurity extends BaseConfig implements SecurityInterface
{
    public function __construct(array|null $config = null)
    {
        // set default
        $this->tokenName = getDotEnv('App.tokenName', 'string', $this->tokenName);
        $this->headerName = getDotEnv('App.headerName', 'string', $this->headerName);
        $this->cookieName = getDotEnv('App.cookieName', 'string', $this->cookieName);
        $this->expires = getDotEnv('App.expires', 'int', $this->expires);
        $this->regenerate = getDotEnv('App.regenerate', 'bool', $this->regenerate);
        $this->redirect = getDotEnv('App.redirect', 'bool', $this->redirect);

        if (!is_null($config))
        {
            $this->rewriteConfig($config);
        }
    }

    //==================================================================

    public string $tokenName = 'X_CSRF_TOKEN';
    public string $headerName = 'X-CSRF-TOKEN';
    public string $cookieName = 'X_CSRF_COOKIE';
    public int $expires = 7200;
    public bool $regenerate = true;
    public bool $redirect = false;

    //==================================================================
}