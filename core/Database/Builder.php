<?php 

declare(strict_types = 1);

namespace Aether\Database;

/** 
 * Aether Query Builder
 * 
 * @class Aether\Database\Builder
**/

abstract class Builder
{
    // sanitizer
    abstract protected function sanitizeColumn(string $column);

    abstract public function select(array $column = [], bool $raw = false);
    abstract public function selectAvg(string $column, string $alias, bool $raw = false);
    abstract public function selectCount(string $column, string $alias, bool $raw = false);
    abstract public function selectMax(string $column, string $alias, bool $raw = false);
    abstract public function selectMin(string $column, string $alias, bool $raw = false);
    abstract public function selectSum(string $column, string $alias, bool $raw = false);
    abstract public function distinct();
    
    abstract public function from(string $tableName, mixed $db, string $DBPrefix);

    abstract public function join(string $table, string $condition, string $joinType = '', bool $raw = false);

    abstract public function innerJoin(string $table, string $condition, bool $raw = false);
    abstract public function outerJoin(string $table, string $condition, bool $raw = false);
    abstract public function leftJoin(string $table, string $condition, bool $raw = false);
    abstract public function rightJoin(string $table, string $condition, bool $raw = false);

    abstract public function where(string $column, string $operator, string|int $value, bool $raw = false);
    abstract public function whereNot(string $column, string $operator, string|int $value, bool $raw = false);
    abstract public function whereIn(string $column, array $value, bool $raw = false);
    abstract public function whereNotIn(string $column, array $value, bool $raw = false);
    abstract public function whereNull(string $column, bool $raw = false);
    abstract public function whereNotNull(string $column, bool $raw = false);

    abstract public function orWhere(string $column, string $operator, string|int $value, bool $raw = false);
    abstract public function orWhereNot(string $column, string $operator, string|int $value, bool $raw = false);
    abstract public function orWhereIn(string $column, array $value, bool $raw = false);
    abstract public function orWhereNotIn(string $column, array $value, bool $raw = false);
    abstract public function orWhereNull(string $column, bool $raw = false);
    abstract public function orWhereNotNull(string $column, bool $raw = false);

    abstract public function whereLike();
    abstract public function notWhereLike();
    abstract public function orWhereLike();
    abstract public function orNotWhereLike();

    abstract public function groupStart();
    abstract public function notGroupStart();
    abstract public function orGroupStart();
    abstract public function orNotGroupStart();
    abstract public function groupEnd();

    abstract public function groupBy();

    abstract public function having();
    abstract public function havingNot();
    abstract public function havingIn();
    abstract public function havingNotIn();
    abstract public function havingNull();
    abstract public function havingNotNull();

    abstract public function orHaving();
    abstract public function orHavingNot();
    abstract public function orHavingIn();
    abstract public function orHavingNotIn();
    abstract public function orHavingNull();
    abstract public function orHavingNotNull();

    abstract public function havingLike();
    abstract public function NotHavingLike();
    abstract public function orHavingLike();
    abstract public function orNotHavingLike();

    abstract public function havingGroupStart();
    abstract public function notHavingGroupStart();
    abstract public function orHavingGroupStart();
    abstract public function orNotHavingGroupStart();
    abstract public function havingGroupEnd();
    
    abstract public function orderBy();

    abstract public function offset();
    abstract public function limit();

    abstract public function countAll();
    abstract public function countAllResults();

    abstract public function get();
    abstract public function getCompiledSelect();

    abstract public function resetQuery();

    abstract public function set();
    abstract public function increment();
    abstract public function decrement();

    abstract public function insert();
    abstract public function insertBatch();
    abstract public function update();
    abstract public function updateBatch();
    abstract public function delete();
    abstract public function replace();

    abstract public function truncate();
    abstract public function emptyTable();
}