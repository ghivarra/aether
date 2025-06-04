<?php 

declare(strict_types = 1);

namespace Aether\Database\Driver;

use Aether\Database\DriverInterface;
use PgSql\Connection as PgSQL;
use PgSql\Result as PgSQLResult;
use Aether\Database\Builder\PostgreSQLBuilder;
use Aether\Exception\SystemException;
use \Throwable;

/** 
 * PostgreSQL Database Driver
 * 
 * @class Aether\Database\Driver\PostgreSQL
**/

class PostgreSQL implements DriverInterface
{
    protected PostgreSQLBuilder $builder;
    protected PgSQL $connection;
    protected PgSQLResult|null|bool $result = null;
    protected array $config = [];
    protected string $defaultConn = '';
    protected string $fallbackMessage = 'Failed to connect to database';

    //===========================================================================================

    public function connect(array $config, string $defaultConn = 'default'): PostgreSQL
    {
        // build config
        $builtConfig = "host={$config['hostname']} port={$config['port']} dbname={$config['database']} user={$config['username']} password={$config['password']} options='--client_encoding={$config['charset']}'";

        // connect
        try {

            $this->connection = pg_connect($builtConfig);

        } catch (Throwable $e) {

            $message = (AETHER_ENV === 'development') ? $e->getMessage() : $this->fallbackMessage;
            throw new SystemException($message, $e->getCode());
        }

        // set config
        $this->config = $config;

        // return
        return $this;
    }

    //===========================================================================================

    public function disconnect(): bool
    {
        return true;
    }

    //===========================================================================================

    public function escape(string|int|float $data): string
    {
        return '';
    }

    //===========================================================================================

    public function getCurrentInstance(): PgSQL
    {
        return $this->connection;    
    }

    //===========================================================================================

    public function getResult(): PgSQLResult
    {
        return $this->result;
    }

    //===========================================================================================

    public function getResultArray(): array
    {
        return [];
    }

    //===========================================================================================

    public function getRowArray(): array | null
    {
        return null;
    }

    //===========================================================================================

    public function table(string $tableName): PostgreSQLBuilder
    {
        $builder = new PostgreSQLBuilder();

        // return
        return $builder;
    }

    //===========================================================================================

    public function transBegin(): PostgreSQL
    {
        return $this;
    }

    //===========================================================================================

    public function transCommit(): PostgreSQL
    {
        return $this;
    }

    //===========================================================================================

    public function transRollback(): PostgreSQL
    {
        return $this;
    }

    //===========================================================================================

    public function preparedQuery(string $query, array $params = []): PostgreSQL
    {
        return $this;
    }
    
    //===========================================================================================

    public function rawQuery($query): PostgreSQL
    {
        return $this;
    }

    //===========================================================================================
}