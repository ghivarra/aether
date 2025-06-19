<?php 

declare(strict_types = 1);

namespace Aether\Config;

use Aether\Interface\Config\AppInterface;

/** 
 * Base App Configurations
 * 
 * @class Aether\Config
 * 
**/

class BaseApp extends BaseConfig implements AppInterface
{
    public string $env = '';
    public string $baseURL = '';
    public string $permittedURIChars = '';
    public string $defaultLocale = '';
    public string $timezone = '';
    public string $encryptionKey = '';

    //==================================================================

    public function __construct(array|null $config = null)
    {
        // set default
        $this->env = getDotEnv('App.env', 'string', $this->env);
        $this->baseURL = getDotEnv('App.baseURL', 'string', $this->baseURL);
        $this->permittedURIChars = getDotEnv('App.permittedURIChars', 'string', $this->permittedURIChars);
        $this->defaultLocale = getDotEnv('App.defaultLocale', 'string', $this->defaultLocale);
        $this->timezone = getDotEnv('App.timezone', 'string', $this->timezone);
        $this->encryptionKey = getDotEnv('App.encryptionKey', 'string', $this->encryptionKey);

        if (!is_null($config))
        {
            $this->rewriteConfig($config);
        }
    }

    //==================================================================
}