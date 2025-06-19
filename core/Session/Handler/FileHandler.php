<?php 

declare(strict_types = 1);

namespace Aether\Session\Handler;

use Aether\Session\CustomHandlerInterface;
use Aether\Session\Handler\SessionHandlerTrait;
use Config\Services;
use Config\Cookie as CookieConfig;
use Config\Session as SessionConfig;

class FileHandler implements CustomHandlerInterface
{
    private $divider = '_';
    private SessionConfig $config;
    private CookieConfig $cookieConfig;
    private string $savePath = '';
    private string $sessionFile = '';
    private $handler = null;

    //========================================================================================================

    use SessionHandlerTrait;

    //========================================================================================================

    public function __construct()
    {
        $this->config = Services::sessionConfig();
        $this->cookieConfig = Services::cookieConfig();
    }

    //========================================================================================================

    public function close(): bool
    {
        // check if handler is still valid
        if ($this->handler)
        {   
            flock($this->handler, LOCK_UN);
            fclose($this->handler);
            $this->handler = null;
        }

        // return true
        return true;
    }

    //========================================================================================================

    public function create_sid(): string
    {
        return $this->config->cookieName . $this->divider . bin2hex(random_bytes(16)) . $this->divider . dechex(time());
    }

    //========================================================================================================

    public function destroy(string $sessionID): bool
    {
        if (file_exists($this->sessionFile))
        {
            unlink($this->sessionFile);
        }

        return true;
    }

    //========================================================================================================

    public function gc(int $maxLifetime): int|false
    {
        $globPath = "{$this->savePath}/{$this->config->cookieName}{$this->divider}*";
        $deleted  = 0;

        // search file
        foreach (glob($globPath) as $file):

            // if it should be deleted
            if (filemtime($file) + $maxLifetime <  time())
            {
                if (unlink($file))
                {
                    $deleted++;
                }
            }

        endforeach;

        // return
        return $deleted;
    }

    //========================================================================================================

    public function open(string $savePath = '', string $sessionName = ''): bool
    {
        // set save path
        $this->savePath = $savePath;

        // set save path and create folder if it doesn't exist
        if (!is_dir($this->savePath))
        {
            mkdir($this->savePath, 0700, true);
        }

        // return true
        return true;
    }

    //========================================================================================================

    public function read(string $sessionID): string|false
    {
        // generate session file path and name
        $this->sessionFile = "{$this->savePath}/{$sessionID}";

        // create new if it doesn't exist
        if (!file_exists($this->sessionFile))
        {
            $time        = time();
            $initialData = "__last_regenerate|i:{$time};";
            $initialData = ($this->config->useEncryption) ? $this->encryptSessionData($initialData) : $initialData;

            file_put_contents($this->sessionFile, $initialData);
        }

        // set handler
        $this->handler = fopen($this->sessionFile, 'c+');

        // return empty if cannot lock and the file handle cannot run
        if ($this->handler === false || !flock($this->handler, LOCK_EX))
        {
            return '';
        }

        // clear cache for this flie and read size for fread
        // also read data
        clearstatcache(true, $this->sessionFile);
        $size = filesize($this->sessionFile);
        $data = ''; // make it empty as default

        if ($size > 0)
        {
            $data = fread($this->handler, $size);
            $data = ($this->config->useEncryption) ? $this->decryptSessionData($data) : $data;
        }
        
        // return data
        return $data;
    }

    //========================================================================================================

    public function write(string $sessionID, string $data): bool
    {
        if (!$this->handler)
        {
            return false;
        }

        // set if should encrypted or not
        $data = ($this->config->useEncryption) ? $this->encryptSessionData($data) : $data;

        // write data
        rewind($this->handler);
        fwrite($this->handler, $data);
        ftruncate($this->handler, strlen($data));

        // return
        return true;
    }

    //========================================================================================================
}
