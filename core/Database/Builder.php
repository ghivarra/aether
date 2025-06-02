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
    abstract public function select();
    abstract public function selectAvg();
    abstract public function selectCount();
    abstract public function selectMax();
    abstract public function selectMin();
    abstract public function selectSum();
    abstract public function distinct();
    
    abstract public function from(string $tableName, mixed $db, string $DBPrefix);
    abstract public function join();

    abstract public function where();
    abstract public function whereNot();
    abstract public function whereIn();
    abstract public function whereNotIn();
    abstract public function whereNull();
    abstract public function whereNotNull();

    abstract public function orWhere();
    abstract public function orWhereNot();
    abstract public function orWhereIn();
    abstract public function orWhereNotIn();
    abstract public function orWhereNull();
    abstract public function orWhereNotNull();

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
    abstract public function getCompiled();

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