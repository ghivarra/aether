<?php 

declare(strict_types = 1);

namespace Aether\Database\Driver;

use \mysqli as SQL;
use \mysqli_driver as SQLDriver;
use \mysqli_result as SQLResult;
use \mysqli_sql_exception as SQLException;
use Aether\Exception\SystemException;
use Aether\Database\Builder\MySQLiBuilder;

/** 
 * MySQLi Database Driver
 * 
 * @class Aether\Database\Driver\MySQLi
**/

class MySQLi
{
    protected MySQLiBuilder|null $builder = null;
    protected SQL|null $connection = null;
    protected SQLDriver|null $connectionDriver = null;
    protected SQLResult|null $result = null;
    protected array $config = [];
    protected string $fallbackMessage = 'Failed to connect to database';

    //===========================================================================================

    public function connect(array $config): MySQLi
    {
        $this->connectionDriver = new SQLDriver();

        // set error report
        if ($config['DBDebug'])
        {
            $this->connectionDriver->report_mode = MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

        } else {

            $this->connectionDriver->report_mode = MYSQLI_REPORT_OFF;
        }

        // try connect
        $mysqli = new SQL($config['hostname'], $config['username'], $config['password'], $config['database'], $config['port']);

        // check if connection failed
        if ($mysqli->connect_errno)
        {
            $message = (AETHER_ENV === 'development') ? "Failed to connect MySQLi Driver: {$mysqli->connect_error}" : $this->fallbackMessage;
            
            throw new SystemException($message, 400);
        }

        

        // set charset
        $mysqli->set_charset($config['charset']);

        // set into SQL
        $this->connection = $mysqli;

        // set config
        $this->config = $config;

        // return instance
        return $this;
    }

    //===========================================================================================

    public function disconnect(): bool
    {   
        // disconnect
        return $this->connection->close();
    }

    //===========================================================================================

    public function escape(mixed $data): string
    {
        $stringData = strval($data);

        // return
        return $this->connection->real_escape_string($stringData);
    }

    //===========================================================================================

    public function getResultArray(): array
    {
        if (is_null($this->result))
        {
            $message = (AETHER_ENV === 'development') ? "Cannot run getResultArray() because query is not yet executed." : 'Failed to fetch data from database.';

            throw new SystemException($message, 400);
        }

        // result
        return $this->result->fetch_all(MYSQLI_ASSOC);
    }

    //===========================================================================================

    public function getRowArray(): array | null
    {
        if (is_null($this->result))
        {
            $message = (AETHER_ENV === 'development') ? "Cannot run getRowArray() because query is not yet executed." : 'Failed to fetch data from database.';

            throw new SystemException($message, 400);
        }

        // return only one
        return $this->result->fetch_array(MYSQLI_ASSOC);
    }

    //===========================================================================================

    public function table(string $tableName): MySQLiBuilder
    {
        // search config
        if (!isset($this->config['DBDriver']) || is_null($this->connection))
        {
            $message = (AETHER_ENV === 'development') ? "You must connect to the database first before setting the table." : $this->fallbackMessage;

            throw new SystemException($message, 400);
        }

        // find class
        $this->builder = new MySQLiBuilder();

        // return builder
        return $this->builder->from($tableName, $this, $this->config['DBPrefix']);
    }

    //===========================================================================================

    public function transBegin(): MySQLi
    {
        // check transaction
        $transaction = $this->connection->begin_transaction();

        if (!$transaction)
        {
            $message = (AETHER_ENV === 'development') ? "The database type does not support transaction, transaction failed." : $this->fallbackMessage;

            throw new SystemException($message, 400);
        }
        
        // return instance
        return $this;
    }

    //===========================================================================================

    public function transCommit(): MySQLi
    {
        // check transaction
        $transaction = $this->connection->commit();

        if (!$transaction)
        {
            $message = (AETHER_ENV === 'development') ? "Cannot commit transaction, commiting transaction failed." : $this->fallbackMessage;

            throw new SystemException($message, 400);
        }
        
        // return instance
        return $this;
    }

    //===========================================================================================

    public function transRollback(): MySQLi
    {
        // check transaction
        $transaction = $this->connection->rollback();

        if (!$transaction)
        {
            $message = (AETHER_ENV === 'development') ? "Cannot rollback transaction, rollback transaction failed." : $this->fallbackMessage;

            throw new SystemException($message, 400);
        }

        // return instance
        return $this;
    }

    //===========================================================================================

    public function preparedQuery(string $query, array $params = []): MySQLi
    {
        // execute
        try {

            $this->result = $this->connection->execute_query($query, $params);

        } catch (SQLException $e) {

            $message = (AETHER_ENV === 'development') ? "Cannot fetch data. Reason: {$e->getMessage()}." : 'Failed to fetch data from database.';

            throw new SystemException($message, 400);
        }

        // return instance
        return $this;
    }

    //===========================================================================================

    public function rawQuery($query): MySQLi
    {
        // execute
        try {

            $this->result = $this->connection->query($query);

        } catch (SQLException $e) {

            $message = (AETHER_ENV === 'development') ? "Cannot fetch data. Reason: {$e->getMessage()}." : 'Failed to fetch data from database.';

            throw new SystemException($message, 400);
        }

        // return instance
        return $this;
    }

    //===========================================================================================
}