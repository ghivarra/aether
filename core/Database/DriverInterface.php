<?php 

declare(strict_types = 1);

namespace Aether\Database;

use Aether\Database\Builder;

/** 
 * Base Database Driver
 * 
 * @class Aether\Database\DriverInterface
**/

interface DriverInterface
{
    public function connect(array $confi, string $defaultConn = 'default'): DriverInterface;

    //===========================================================================================

    public function disconnect(): bool;

    //===========================================================================================

    public function escape(string|int|float $data, mixed $option = null): string|int;

    //===========================================================================================

    public function getCurrentInstance();

    //===========================================================================================

    public function getResult();

    //===========================================================================================

    public function getResultArray(): array;

    //===========================================================================================

    public function getRowArray(): array | null;

    //===========================================================================================

    public function table(string $tableName): Builder;

    //===========================================================================================

    public function transBegin(): DriverInterface;

    //===========================================================================================

    public function transCommit(): DriverInterface;

    //===========================================================================================

    public function transRollback(): DriverInterface;

    //===========================================================================================

    public function transStatus(): bool;

    //===========================================================================================

    public function preparedQuery(string $query, array $params = []): DriverInterface;
    
    //===========================================================================================

    public function rawQuery($query): DriverInterface;

    //===========================================================================================
}