<?php namespace Aether\Config;

/** 
 * Base App Configurations
 * 
 * @class Aether\Config
 * 
 * You can ignore this config if you wanted to use dotenv file
**/

class BaseApp
{
    public function __construct()
    {
        // set default
        $this->env = getDotEnv('App.env', 'string', $this->env);
        $this->baseURL = getDotEnv('App.baseURL', 'string', $this->baseURL);
        $this->permittedURIChars = getDotEnv('App.permittedURIChars', 'string', $this->permittedURIChars);
        $this->defaultLocale = getDotEnv('App.defaultLocale', 'string', $this->defaultLocale);
        $this->timezone = getDotEnv('App.timezone', 'string', $this->timezone);
    }

    //==================================================================

    public string $env = '';
    public string $baseURL = '';
    public string $permittedURIChars = '';
    public string $defaultLocale = '';
    public string $timezone = '';

    //==================================================================
}