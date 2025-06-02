<?php 

declare(strict_types = 1);

namespace Aether\Database\Builder;

use Aether\Database\Builder;
use Aether\Database\BaseBuilderTrait;

class MySQLiBuilder extends Builder
{
    use BaseBuilderTrait;

    //=================================================================================================

    public function select(array $fields = [], bool $raw = false): MySQLiBuilder
    {
        foreach ($fields as $field):

            if (!$raw)
            {
                // escape field
                $field = $this->db->escape($field);

                // get
                if (!str_contains($field, '`'))
                {
                    if (str_contains($field, '.'))
                    {
                        if ($this->from !== substr($field, 0, strlen($this->from)))
                        {
                            $field = "`{$this->from}" . str_replace('.', '`.', $field);
    
                        } else {
    
                            $field = "`" . str_replace('.', '`.', $field);
                        }

                    } else {

                        $field = "`{$this->from}`.{$field}";
                    }
                }
            }

            array_push($this->selectCollection, $field);

        endforeach;

        // return instance
        return $this;
    }

    //=================================================================================================
    
    public function selectAvg() {}
    public function selectCount() {}
    public function selectMax() {}
    public function selectMin() {}
    public function selectSum() {}
    public function distinct() {}

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