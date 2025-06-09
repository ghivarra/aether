<?php 

declare(strict_types = 1);

namespace Aether\Session\Handler;

use \Throwable;
use Aether\Exception\SystemException;

trait SessionHandlerTrait
{
    protected function decryptSessionData(string $data): string
    {
        // throw error if encryption key is not supplied
        if (empty($this->config->encryptionKey))
        {
            $message = (AETHER_ENV === 'development') ? 'The encryption should be set on session config' : 'Failed to fetch session';
            throw new SystemException($message, 500);
        }

        // try and catch error when decrypting data
        try {

            $data = decrypt($data, $this->config->encryptionKey);

        } catch (Throwable $e) {

            $message = (AETHER_ENV === 'development') ? $e->getMessage() : "Failed to fetch session";
            $code    = (AETHER_ENV === 'development') ? $e->getCode() : 500;

            throw new SystemException($message, $code);
        }

        // return
        return $data;
    }

    //========================================================================================================

    protected function encryptSessionData(string $data): string
    {
        // throw error if encryption key is not supplied
        if (empty($this->config->encryptionKey))
        {
            $message = (AETHER_ENV === 'development') ? 'The encryption should be set on session config' : 'Failed to fetch session';
            throw new SystemException($message, 500);
        }

        // try and catch error when encrypting data
        try {

            $data = encrypt($data, $this->config->encryptionKey);

        } catch (Throwable $e) {

            $message = (AETHER_ENV === 'development') ? $e->getMessage() : "Failed to fetch session";
            $code    = (AETHER_ENV === 'development') ? $e->getCode() : 500;

            throw new SystemException($message, $code);
        }

        // return
        return $data;
    }

    //========================================================================================================
}