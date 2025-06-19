<?php 

declare(strict_types = 1);

namespace Aether\Session\Handler;

use Aether\Database\DriverInterface;
use Aether\Session\CustomHandlerInterface;
use Aether\Session\Handler\SessionHandlerTrait;
use Aether\Database;
use Config\Session as SessionConfig;
use Config\Cookie as CookieConfig;
use Config\Database as DBConfig;
use \Throwable;
use Aether\Exception\SystemException;
use Config\Services;

class DatabaseHandler implements CustomHandlerInterface
{
    private $divider = '_';
    private SessionConfig $config;
    private CookieConfig $cookieConfig;
    private DBConfig $DBConfig;
    private array $DBInstance;
    private string $defaultConnection = '';
    private string $tableName = '';
    private string $sessionFile = '';
    private DriverInterface|null $db = null;

    //========================================================================================================

    use SessionHandlerTrait;

    //========================================================================================================

    private function createTable(): bool
    {
        if ($this->DBInstance['DBDriver'] === 'PostgreSQL')
        {
            $table    = $this->db->escape($this->tableName, 'literal');
            $datetime = "TIMESTAMP";
            $current  = "NOW()";

        } elseif ($this->DBInstance['DBDriver'] === 'MySQLi') {

            $table    = $this->db->escape($this->tableName);
            $datetime = "DATETIME";
            $current  = "CURRENT_TIMESTAMP";
        }

        // create table
        $this->db->rawQuery("CREATE TABLE {$table} (id VARCHAR(256) PRIMARY KEY, data TEXT NOT NULL, created_at {$datetime} NOT NULL DEFAULT {$current}, updated_at {$datetime} NOT NULL DEFAULT {$current})");

        // return
        return true;
    }

    //========================================================================================================

    public function __construct(string|null $defaultConnection = null)
    {
        $this->config = Services::sessionConfig();
        $this->cookieConfig = Services::cookieConfig();
        $this->DBConfig = Services::databaseConfig();

        // set default connection
        $this->defaultConnection = is_null($defaultConnection) ? $this->DBConfig->defaultDB : $defaultConnection;
        $this->DBInstance = $this->DBConfig->{$this->defaultConnection};

        // connect
        $this->db = Database::connect($this->defaultConnection);

        // set table name
        $this->tableName = "{$this->DBInstance['DBPrefix']}{$this->config->savePath}";
    }

    //========================================================================================================

    public function close(): bool
    {
        // check if db is still valid
        if ($this->db)
        {   
            $conn = $this->db->getCurrentInstance();
            $conn->commit();
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
        $this->db->table($this->tableName)
                 ->where('id', '=', $sessionID)
                 ->delete();

        // return
        return true;
    }

    //========================================================================================================

    public function gc(int $maxLifetime): int|false
    {
        $builder = $this->db->table($this->tableName)
                            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime("-{$maxLifetime} seconds")));

        $deleted = $builder->countAllResults(false);
        
        // delete action if more than one
        if ($deleted > 0)
        {
            $builder->delete();
        }

        // return
        return $deleted;
    }

    //========================================================================================================

    public function open(string $tableName = '', string $sessionName = ''): bool
    {
        // ignore table name and set based on config
        $this->tableName = $this->DBInstance['DBPrefix'] . $this->config->savePath;

        // return true
        return true;
    }

    //========================================================================================================

    public function read(string $sessionID): string|false
    {
        try {

            // check table
            $query = $this->db->table($this->tableName)
                              ->select(['data'])
                              ->where('id', '=', $sessionID)
                              ->limit(1)
                              ->getCompiledSelect();
  
        } catch (Throwable $e) {

            // create table
            $this->createTable();

            // try again
            $message = (AETHER_ENV === 'development') ? "The session table has not been created yet, it is automatically created as we generated this error, try again" : "Failed to fetch session data";

            // throw
            throw new SystemException($message, 500);
        }
        
        // if empty then create new session
        $session = $this->db->rawQuery("{$query} FOR UPDATE")->getRowArray();

        // create new if it doesn't exist
        if (empty($session))
        {
            $time         = time();
            $originalData = "__last_regenerate|i:{$time};";
            $initialData  = ($this->config->useEncryption) ? $this->encryptSessionData($originalData) : $originalData;
            $currentTime  = date('Y-m-d H:i:s');

            // create session
            $this->db->table($this->tableName)
                     ->insert([
                        'id'         => $sessionID,
                        'data'       => $initialData,
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime,
                     ]);

            // return original data
            return $originalData;

        } else {

            // return data from db
            return ($this->config->useEncryption) ? $this->decryptSessionData($session['data']) : $session['data'];
        }
    }

    //========================================================================================================

    public function write(string $sessionID, string $data): bool
    {
        if (!$this->db)
        {
            return false;
        }

        // set if should encrypted or not
        $data = ($this->config->useEncryption) ? $this->encryptSessionData($data) : $data;

        // write data
        $this->db->table($this->tableName)
                 ->where('id', '=', $sessionID)
                 ->update([
                    'data' => $data
                 ]);

        // return
        return true;
    }

    //========================================================================================================
}
