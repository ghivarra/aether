<?php 

declare(strict_types = 1);

namespace Aether\Database\Driver;

use \mysqli as SQL;
use \mysqli_driver as SQLDriver;
use \mysqli_result as SQLResult;
use \mysqli_sql_exception as SQLException;
use Aether\Exception\SystemException;
use Aether\Database\Builder\MySQLiBuilder;
use Aether\Database\DriverInterface;
use Aether\Database;

/** 
 * MySQLi Database Driver
 * 
 * @class Aether\Database\Driver\MySQLi
**/

class MySQLi implements DriverInterface
{
    protected MySQLiBuilder|null $builder = null;
    protected SQL|null $connection = null;
    protected SQLDriver|null $connectionDriver = null;
    protected SQLResult|null|bool $result = null;
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
            
            throw new SystemException($message, 500);
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
        $this->connection->close();

        // empty
        $this->builder = null;
        $this->connection = null;
        $this->connectionDriver = null;
        $this->result = null;

        // return
        return true;
    }

    //===========================================================================================

    public function escape(string|int|float $data): string
    {
        // convert into string
        // return
        return $this->connection->real_escape_string(strval($data));
    }

    //===========================================================================================

    public function getResultArray(): array
    {
        if (is_null($this->result))
        {
            $message = (AETHER_ENV === 'development') ? "Cannot run getResultArray() because query is not yet executed." : 'Failed to fetch data from database.';

            throw new SystemException($message, 500);
        }

        // result
        $result = $this->result->fetch_all(MYSQLI_ASSOC);

        // free result
        $this->result->free();

        // return result
        return $result;
    }

    //===========================================================================================

    public function getRowArray(): array | null
    {
        if (is_null($this->result))
        {
            $message = (AETHER_ENV === 'development') ? "Cannot run getRowArray() because query is not yet executed." : 'Failed to fetch data from database.';

            throw new SystemException($message, 500);
        }

        // return only one
        $result = $this->result->fetch_array(MYSQLI_ASSOC);

        // free result
        $this->result->free();

        // return result
        return $result;
    }

    //===========================================================================================

    public function table(string $tableName): MySQLiBuilder
    {
        // search config
        if (!isset($this->config['DBDriver']) || is_null($this->connection))
        {
            $message = (AETHER_ENV === 'development') ? "You must connect to the database first before setting the table." : $this->fallbackMessage;

            throw new SystemException($message, 500);
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

            throw new SystemException($message, 500);
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

            throw new SystemException($message, 500);
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

            throw new SystemException($message, 500);
        }

        // return instance
        return $this;
    }

    //===========================================================================================

    public function preparedQuery(string $query, array $params = []): MySQLi
    {
        if (AETHER_ENV === 'development')
        {
            // store time
            $timeStart = hrtime(true);
        }

        // execute
        try {

            $this->result = $this->connection->execute_query($query, $params);

            // store for debugging purposes
            if (AETHER_ENV === 'development')
            {
                $variable = [
                    '(?,', '?,', '?),', '?)'
                ];

                // explode query
                $queryArray = explode(" ", $query);

                // walk array to convert the question mark into params
                foreach ($queryArray as $i => $item):

                    if (in_array($item, $variable))
                    {
                        $key   = array_search($item, $variable);
                        $param = "'" . $this->escape(array_shift($params)) . "'";
                        $value = str_replace('?', $param, $variable[$key]);

                        // store
                        $queryArray[$i] = $value;
                    }

                endforeach;
                
                // get diff
                $fullQuery = implode(" ", $queryArray);
                $timeEnd   = hrtime(true);
                $timeDiff  = round((($timeEnd - $timeStart) / 1000000), 2);

                // get backtrace
                $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);

                // store
                Database::storeQuery($fullQuery, $timeDiff, $backtrace);
            }

        } catch (SQLException $e) {

            $message = (AETHER_ENV === 'development') ? "Cannot fetch data. Reason: {$e->getMessage()}." : 'Failed to fetch data from database.';

            throw new SystemException($message, 500);
        }

        // return instance
        return $this;
    }

    //===========================================================================================

    public function rawQuery($query): MySQLi
    {
        if (AETHER_ENV === 'development')
        {
            // store time
            $timeStart = hrtime(true);
        }

        // execute
        try {

            $this->result = $this->connection->query($query);

            // store for debugging purposes
            if (AETHER_ENV === 'development')
            {
                // get diff
                $timeEnd   = hrtime(true);
                $timeDiff  = round((($timeEnd - $timeStart) / 1000000), 2);

                // get backtrace
                $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);

                // store
                Database::storeQuery($query, $timeDiff, $backtrace);
            }

        } catch (SQLException $e) {

            $message = (AETHER_ENV === 'development') ? "Cannot fetch data. Reason: {$e->getMessage()}." : 'Failed to fetch data from database.';

            throw new SystemException($message, 500);
        }

        // return instance
        return $this;
    }

    //===========================================================================================

    public function getCurrentInstance(): SQL
    {
        return $this->connection;
    }

    //===========================================================================================

    public function getResult(): SQLResult|null|bool
    {
        return $this->result;
    }

    //===========================================================================================
}