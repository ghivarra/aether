<?php 

declare(strict_types = 1);

namespace Aether\Database\Driver;

use Aether\Database\Builder;

/** 
 * Base Database Driver
 * 
 * @class Aether\Database\BaseDriver
**/

interface DriverInterface
{
    public function connect(array $config): DriverInterface;

    //===========================================================================================

    public function disconnect(): bool;

    //===========================================================================================

    public function escape(mixed $data): string;

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

    public function preparedQuery(string $query, array $params = []): DriverInterface;
    
    //===========================================================================================

    public function rawQuery($query): DriverInterface;

    //===========================================================================================
}