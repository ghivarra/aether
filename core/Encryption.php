<?php

declare(strict_types = 1);

namespace Aether;

use Config\App as AppConfig;

class Encryption
{
    protected string $key = '';

    //=========================================================================================

    public function __construct(AppConfig|null $config = null)
    {
        $config    = is_null($config) ? new AppConfig() : $config;
        $this->key = $config->encryptionKey;
    }

    //=========================================================================================

    public function encrypt(string $data, string $key = '', string $hashAlgo = 'sha256', string $encryptAlgo = 'AES-256-CBC'): string
    {
        $key = ($key === '') ? $this->key : $key;

        // return
        return encrypt($data, $key, $hashAlgo, $encryptAlgo);
    }

    //=========================================================================================

    public function decrypt(string $data, string $key = '', string $hashAlgo = 'sha256', string $encryptAlgo = 'AES-256-CBC'): string
    {
        $key = ($key === '') ? $this->key : $key;

        // return
        return decrypt($data, $key, $hashAlgo, $encryptAlgo);
    }

    //=========================================================================================
}