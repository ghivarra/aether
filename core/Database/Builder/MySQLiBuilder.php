<?php 

declare(strict_types = 1);

namespace Aether\Database\Builder;

use Aether\Database\Builder;
use Aether\Database\BaseBuilderTrait;

class MySQLiBuilder extends Builder
{
    use BaseBuilderTrait;

    //=================================================================================================

    protected function sanitizeColumn(string $column): string
    {
        // escape column
        $column = $this->db->escape($column);

        // get
        if (!str_contains($column, '`'))
        {
            if (str_contains($column, '.'))
            {
                if ($this->from !== substr($column, 0, strlen($this->from)))
                {
                    $column = "`{$this->from}" . str_replace('.', '`.', $column);

                } else {

                    $column = "`" . str_replace('.', '`.', $column);
                }

            } else {

                $column = "`{$this->from}`.{$column}";
            }
        }

        // return
        return $column;
    }

    //=================================================================================================

    public function select(array $columns = [], bool $raw = false): MySQLiBuilder
    {
        foreach ($columns as $column):

            if (!$raw)
            {
                $column = $this->sanitizeColumn($column);
            }

            array_push($this->selectCollection, $column);

        endforeach;

        // return instance
        return $this;
    }

    //=================================================================================================
    
    public function selectAvg(string $column, string $alias, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias);
        }

        array_push($this->selectCollection, "AVG({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectCount(string $column, string $alias, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias);
        }

        array_push($this->selectCollection, "COUNT({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectMax(string $column, string $alias, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias);
        }

        array_push($this->selectCollection, "MAX({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectMin(string $column, string $alias, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias);
        }

        array_push($this->selectCollection, "MIN({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectSum(string $column, string $alias, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias);
        }

        array_push($this->selectCollection, "SUM({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function distinct(): MySQLiBuilder
    {
        $this->distinct = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function from(string $tableName, mixed $db, string $DBPrefix): MySQLiBuilder
    {
        // set connection
        $this->db = $db;
        $this->prefix = $DBPrefix;

        // set table
        $this->from = "{$DBPrefix}{$tableName}";
        
        // return instance
        return $this;
    }

    //=================================================================================================

    public function join() {}

    public function where() {}
    public function whereNot() {}
    public function whereIn() {}
    public function whereNotIn() {}
    public function whereNull() {}
    public function whereNotNull() {}

    public function orWhere() {}
    public function orWhereNot() {}
    public function orWhereIn() {}
    public function orWhereNotIn() {}
    public function orWhereNull() {}
    public function orWhereNotNull() {}

    public function whereLike() {}
    public function notWhereLike() {}
    public function orWhereLike() {}
    public function orNotWhereLike() {}

    public function groupStart() {}
    public function notGroupStart() {}
    public function orGroupStart() {}
    public function orNotGroupStart() {}
    public function groupEnd() {}

    public function groupBy() {}

    public function having() {}
    public function havingNot() {}
    public function havingIn() {}
    public function havingNotIn() {}
    public function havingNull() {}
    public function havingNotNull() {}

    public function orHaving() {}
    public function orHavingNot() {}
    public function orHavingIn() {}
    public function orHavingNotIn() {}
    public function orHavingNull() {}
    public function orHavingNotNull() {}

    public function havingLike() {}
    public function NotHavingLike() {}
    public function orHavingLike() {}
    public function orNotHavingLike() {}

    public function havingGroupStart() {}
    public function notHavingGroupStart() {}
    public function orHavingGroupStart() {}
    public function orNotHavingGroupStart() {}
    public function havingGroupEnd() {}
    
    public function orderBy() {}

    public function offset() {}
    public function limit() {}

    public function countAll() {}
    public function countAllResults() {}

    public function get() {}
    public function getCompiled() {}

    public function resetQuery() {}

    public function set() {}
    public function increment() {}
    public function decrement() {}

    public function insert() {}
    public function insertBatch() {}
    public function update() {}
    public function updateBatch() {}
    public function delete() {}
    public function replace() {}

    public function truncate() {}
    public function emptyTable() {}
}