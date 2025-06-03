<?php 

declare(strict_types = 1);

namespace Aether\Database;

use Aether\Database\DriverInterface;

/** 
 * Aether Query Builder
 * 
 * @class Aether\Database\Builder
**/

abstract class Builder
{
    // sanitizer
    abstract protected function sanitizeColumn(string $column): string;
    abstract protected function sanitizeTable(string $table): string;

    abstract public function select(array $column = [], bool $raw = false): Builder;
    abstract public function selectAvg(string $column, string $alias, bool $raw = false): Builder;
    abstract public function selectCount(string $column, string $alias, bool $raw = false): Builder;
    abstract public function selectMax(string $column, string $alias, bool $raw = false): Builder;
    abstract public function selectMin(string $column, string $alias, bool $raw = false): Builder;
    abstract public function selectSum(string $column, string $alias, bool $raw = false): Builder;
    abstract public function distinct(): Builder;
    
    abstract public function from(string $tableName, mixed $db, string $DBPrefix): Builder;

    abstract public function join(string $table, string|array $condition, string $joinType = '', bool $raw = false): Builder;

    abstract public function innerJoin(string $table, string|array $condition, bool $raw = false): Builder;
    abstract public function outerJoin(string $table, string|array $condition, bool $raw = false): Builder;
    abstract public function leftJoin(string $table, string|array $condition, bool $raw = false): Builder;
    abstract public function rightJoin(string $table, string|array $condition, bool $raw = false): Builder;

    abstract public function where(string $column, string $operator, string|int $value, bool $raw = false): Builder;
    abstract public function whereNot(string $column, string $operator, string|int $value, bool $raw = false): Builder;
    abstract public function whereIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function whereNotIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function whereNull(string $column, bool $raw = false): Builder;
    abstract public function whereNotNull(string $column, bool $raw = false): Builder;

    abstract public function orWhere(string $column, string $operator, string|int $value, bool $raw = false): Builder;
    abstract public function orWhereNot(string $column, string $operator, string|int $value, bool $raw = false): Builder;
    abstract public function orWhereIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function orWhereNotIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function orWhereNull(string $column, bool $raw = false): Builder;
    abstract public function orWhereNotNull(string $column, bool $raw = false): Builder;

    abstract public function whereLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;
    abstract public function whereNotLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;
    abstract public function orWhereLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;
    abstract public function orWhereNotLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;

    abstract public function groupStart(): Builder;
    abstract public function notGroupStart(): Builder;
    abstract public function orGroupStart(): Builder;
    abstract public function orNotGroupStart(): Builder;
    abstract public function groupEnd(): Builder;

    abstract public function groupBy(string|array $columns, bool $raw = false): Builder;

    abstract public function having(string $column, string $operator, string|int $value, bool $raw = false): Builder;
    abstract public function havingNot(string $column, string $operator, string|int $value, bool $raw = false): Builder;
    abstract public function havingIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function havingNotIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function havingNull(string $column, bool $raw = false): Builder;
    abstract public function havingNotNull(string $column, bool $raw = false): Builder;

    abstract public function orHaving(string $column, string $operator, string $value, bool $raw = false): Builder;
    abstract public function orHavingNot(string $column, string $operator, string $value, bool $raw = false): Builder;
    abstract public function orHavingIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function orHavingNotIn(string $column, array $value, bool $raw = false): Builder;
    abstract public function orHavingNull(string $column, bool $raw = false): Builder;
    abstract public function orHavingNotNull(string $column, bool $raw = false): Builder;

    abstract public function havingLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;
    abstract public function havingNotLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;
    abstract public function orHavingLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;
    abstract public function orHavingNotLike(string $column, string $value, string $method = 'both', bool $raw = false): Builder;

    abstract public function havingGroupStart(): Builder;
    abstract public function havingNotGroupStart(): Builder;
    abstract public function havingOrGroupStart(): Builder;
    abstract public function havingOrNotGroupStart(): Builder;
    abstract public function havingGroupEnd(): Builder;
    
    abstract public function orderBy(string $column, string $order = 'ASC', bool $raw = false): Builder;

    abstract public function offset(int $num): Builder;
    abstract public function limit(int $num): Builder;

    abstract public function countAll(): int;
    abstract public function countAllResults(): int;

    abstract public function get(): DriverInterface;
    abstract public function getCompiledSelect(): string;

    abstract public function resetQuery(): Builder;

    abstract public function set(string|array $data, string|int|null|bool $value = false): Builder;
    abstract public function setReplace(string|array $data, string|int|null $oldValue = '', string|int|null $newValue = '', bool $raw = false): Builder;
    abstract public function increment(string $column, int $incNum = 1, bool $raw = false): Builder;
    abstract public function decrement(string $column, int $incNum = 1, bool $raw = false): Builder;

    abstract public function insert(array $data = []): array;
    abstract public function insertBatch(): bool;
    abstract public function update(array $data): array;
    abstract public function updateBatch(): bool;
    abstract public function delete(): array;
    abstract public function replace(array $data = []): array;

    abstract public function truncate(): bool;
    abstract public function emptyTable(): bool;
}