<?php 

declare(strict_types = 1);

namespace Aether\Database\Driver;

use Aether\Database\DriverInterface;
use PgSql\Connection as PgSQL;
use PgSql\Result as PgSQLResult;
use Aether\Database\Builder\PostgreSQLBuilder;
use Aether\Exception\SystemException;
use \Throwable;
use Aether\Database;

/** 
 * PostgreSQL Database Driver
 * 
 * @class Aether\Database\Driver\PostgreSQL
**/

class PostgreSQL implements DriverInterface
{
    protected PostgreSQLBuilder|null $builder;
    protected PgSQL|null $connection;
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

        // set default connection
        $this->defaultConn = $defaultConn;

        // return
        return $this;
    }

    //===========================================================================================

    public function disconnect(): bool
    {
        // disconnect
        pg_close($this->connection);

        // clear on database static data
        if (isset(Database::$currentConnection[$this->defaultConn]))
        {
            unset(Database::$currentConnection[$this->defaultConn]);
        }

        // empty
        $this->defaultConn = '';
        $this->config = [];
        $this->builder = null;
        $this->connection = null;
        $this->result = null;

        // return
        return true;
    }

    //===========================================================================================

    public function escape(string|int|float|null $data, mixed $option = 'string'): string|int
    {
        if (is_int($data))
        {
            return $data;
        }

        switch ($option) {
            case 'literal':
                return pg_escape_literal($this->connection, strval($data));
                break;

            case 'bytea':
                return pg_escape_bytea($this->connection, strval($data));
                break;

            case 'identifier':
                return pg_escape_identifier($this->connection, strval($data));
                break;

            case 'string':
                return pg_escape_string($this->connection, strval($data));
                break;
            
            default:
                return pg_escape_string($this->connection, strval($data));
                break;
        }
    }

    //===========================================================================================

    public function getCurrentInstance(): PgSQL
    {
        return $this->connection;    
    }

    //===========================================================================================

    public function getResult(): array
    {
        return [
            'insert_id'     => pg_last_oid($this->result),
            'affected_rows' => pg_affected_rows($this->result),
            'error'         => pg_result_error($this->result),
        ];
    }

    //===========================================================================================

    public function getResultArray(): array
    {
        // parse result
        $result = pg_fetch_all($this->result, PGSQL_ASSOC);

        // free data
        pg_free_result($this->result);

        // return
        return $result;
    }

    //===========================================================================================

    public function getRowArray(): array | null
    {
        // parse result
        $result = pg_fetch_assoc($this->result);

        // free data
        pg_free_result($this->result);

        // return
        return (!$result) ? null : $result;
    }

    //===========================================================================================

    public function table(string $tableName): PostgreSQLBuilder
    {
        $this->builder = new PostgreSQLBuilder();

        // return
        return $this->builder->from($tableName, $this, $this->config['DBPrefix']);
    }

    //===========================================================================================

    public function transBegin(): PostgreSQL
    {
        try {

            pg_query($this->connection, "BEGIN");

        } catch (Throwable $e) {

            $message = (AETHER_ENV === 'development') ? $e->getMessage() : "Failed to open transaction in database.";
            throw new SystemException($message, $e->getCode());
        }

        // return
        return $this;
    }

    //===========================================================================================

    public function transCommit(): PostgreSQL
    {
        try {

            pg_query($this->connection, "COMMIT");

        } catch (Throwable $e) {

            $message = (AETHER_ENV === 'development') ? $e->getMessage() : "Failed to commit transaction in database.";
            throw new SystemException($message, $e->getCode());
        }

        // return
        return $this;
    }

    //===========================================================================================

    public function transRollback(): PostgreSQL
    {
        try {

            pg_query($this->connection, "ROLLBACK");

        } catch (Throwable $e) {

            $message = (AETHER_ENV === 'development') ? $e->getMessage() : "Failed to rollback transaction in database.";
            throw new SystemException($message, $e->getCode());
        }

        // return
        return $this;
    }

    //===========================================================================================

    public function transStatus(): bool
    {
        // get transaction status
        $status = pg_transaction_status($this->connection);

        // set on true / false
        // so we can conclude the transaction should be committed or rollback-ed
        return ($status === PGSQL_TRANSACTION_INERROR || $status === PGSQL_TRANSACTION_UNKNOWN) ? false : true;
    }

    //===========================================================================================

    public function preparedQuery(string $query, array $params = []): PostgreSQL
    {
        if ($this->config['DBDebug'])
        {
            // store time
            $timeStart = hrtime(true);
        }

        try {

            $this->result = pg_query_params($this->connection, $query, $params);

            // store for debugging purposes
            if ($this->config['DBDebug'])
            {
                // get diff
                $fullQuery = $this->builder->compilePreparedQuery($query, $params);
                $timeEnd   = hrtime(true);
                $timeDiff  = round((($timeEnd - $timeStart) / 1000000), 2);

                // get backtrace
                $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);

                // store
                Database::storeQuery($fullQuery, $timeDiff, $backtrace);
            }

        } catch (Throwable $e) {

            $message = (AETHER_ENV === 'development') ? $e->getMessage() : "Failed to fetch data.";
            throw new SystemException($message, $e->getCode());
        }

        // return instance
        return $this;
    }
    
    //===========================================================================================

    public function rawQuery($query): PostgreSQL
    {
        if ($this->config['DBDebug'])
        {
            // store time
            $timeStart = hrtime(true);
        }

        // execute
        try {

            $this->result = pg_query($this->connection, $query);

            // store for debugging purposes
            if ($this->config['DBDebug'])
            {
                // get diff
                $timeEnd   = hrtime(true);
                $timeDiff  = round((($timeEnd - $timeStart) / 1000000), 2);

                // get backtrace
                $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);

                // store
                Database::storeQuery($query, $timeDiff, $backtrace);
            }

        } catch (Throwable $e) {

            $message = ($this->config['DBDebug']) ? "Cannot fetch data. Reason: {$e->getMessage()}." : 'Failed to fetch data from database.';

            throw new SystemException($message, 500);
        }

        // return instance
        return $this;
    }

    //===========================================================================================
}