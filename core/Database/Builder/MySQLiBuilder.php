<?php 

declare(strict_types = 1);

namespace Aether\Database\Builder;

use Aether\Database\Builder;
use Aether\Database\BaseBuilderTrait;
use Aether\Database\Driver\MySQLi;
use Aether\Exception\SystemException;

class MySQLiBuilder extends Builder
{
    use BaseBuilderTrait;

    protected MySQLi|null $db = null;
    protected array $allowedJoinType = [
        'inner' => 'INNER',
        'left'  => 'LEFT',
        'right' => 'RIGHT',
    ];
    protected array $allowedComparisonOperator = [
        '=', '!=', '>', '<', '<=', '>=', '<>'
    ];

    //=================================================================================================

    protected function sanitizeColumn(string $column): string
    {
        // remove backticks
        $column = str_replace('`', '', $column);

        // escape column
        $column = $this->db->escape($column);

        // get
        if (!str_contains($column, '`'))
        {
            if (str_contains($column, '.'))
            {
                if ($this->prefix !== substr($column, 0, strlen($this->prefix)))
                {
                    $column = "`{$this->prefix}" . str_replace('.', '`.', $column);

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

    protected function sanitizeTable(string $table): string
    {
        // trim table
        $table = empty($table) ? '' : ltrim($table);

        // add prefixes if not set
        if ($this->prefix !== substr($table, 0, strlen($this->prefix)))
        {
            $table = $this->prefix . $table;
        }

        // return
        return $table;
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
        $this->from = $this->sanitizeTable($tableName);
        
        // return instance
        return $this;
    }

    //=================================================================================================

    public function join(string $table, string $condition, string $joinType = '', bool $raw = false): MySQLiBuilder
    {
        if (!empty($joinType))
        {
            if (!in_array(strtolower($joinType), array_keys($this->allowedJoinType)))
            {
                $joinType = esc($joinType);
                throw new SystemException("{$joinType} is not allowed", 400);
            }
        }

        // sanitize data
        $data = [
            'type'      => empty($joinType) ? null : strtolower($joinType),
            'table'     => ($raw) ? $table : $this->sanitizeTable($table),
            'condition' => ($raw) ? $condition : $this->addPrefix($condition),
        ];

        // push data
        array_push($this->joinCollection, $data);

        // return instance
        return $this;
    }

    //=================================================================================================

    public function innerJoin(string $table, string $condition, bool $raw = false): MySQLiBuilder
    {
        // return instance
        return $this->join($table, $condition, 'inner', $raw);
    }

    //=================================================================================================

    public function OuterJoin(string $table, string $condition, bool $raw = false): MySQLiBuilder
    {
        // return instance
        return $this->join($table, $condition, 'outer', $raw);
    }

    //=================================================================================================

    public function leftJoin(string $table, string $condition, bool $raw = false): MySQLiBuilder
    {
        // return instance
        return $this->join($table, $condition, 'left', $raw);
    }

    //=================================================================================================

    public function rightJoin(string $table, string $condition, bool $raw = false): MySQLiBuilder
    {
        // return instance
        return $this->join($table, $condition, 'right', $raw);
    }

    //=================================================================================================

    protected function beforeWhere(string $column, string $operator, string|int $value, bool $raw = false): array
    {
        if (!in_array($operator, $this->allowedComparisonOperator))
        {
            if (!empty($joinType))
            {
                if (!in_array(strtolower($joinType), array_keys($this->allowedJoinType)))
                {
                    $joinType = esc($joinType);
                    throw new SystemException("{$joinType} as a comparison operator is not allowed", 400);
                }
            }
        }

        // return
        return [
            'column' => ($raw) ? $column : $this->sanitizeColumn($column),
            'value'  => ($raw) ? $value : $this->db->escape($value),
        ];
    }

    //=================================================================================================

    public function where(string $column, string $operator, string|int $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE {$data['column']} {$operator} ?");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND {$data['column']} {$operator} ?");

            } else {

                array_push($this->whereCollection, "{$data['column']} {$operator} ?");
            }
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================
    
    public function whereNot(string $column, string $operator, string|int $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE NOT {$data['column']} {$operator} ?");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND NOT {$data['column']} {$operator} ?");

            } else {

                array_push($this->whereCollection, "NOT {$data['column']} {$operator} ?");
            }
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function whereIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            
            foreach ($value as $n => $item):

                $value[$n] = $this->db->escape($item);

            endforeach;
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE {$column} IN ($variables)");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND {$column} IN ($variables)");

            } else {

                array_push($this->whereCollection, "{$column} IN ($variables)");
            }            
        }

        // push value as parameters
        foreach ($value as $n => $item):

            array_push($this->preparedParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function whereNotIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            
            foreach ($value as $n => $item):

                $value[$n] = $this->db->escape($item);

            endforeach;
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE {$column} NOT IN ($variables)");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND {$column} NOT IN ($variables)");

            } else {

                array_push($this->whereCollection, "{$column} NOT IN ($variables)");
            }   
        }

        // push value as parameters
        foreach ($value as $n => $item):

            array_push($this->preparedParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function whereNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE {$column} IS NULL");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND {$column} IS NULL");

            } else {

                array_push($this->whereCollection, "{$column} IS NULL");
            }   
        }

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function whereNotNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE {$column} IS NOT NULL");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND {$column} IS NOT NULL");

            } else {

                array_push($this->whereCollection, "{$column} IS NOT NULL");
            } 
        }

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhere(string $column, string $operator, string|int $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR {$data['column']} {$operator} ?");

        } else {

            array_push($this->whereCollection, "{$data['column']} {$operator} ?");
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereNot(string $column, string $operator, string|int $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR NOT {$data['column']} {$operator} ?");

        } else {

            array_push($this->whereCollection, "NOT {$data['column']} {$operator} ?");
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            
            foreach ($value as $n => $item):

                $value[$n] = $this->db->escape($item);

            endforeach;
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR {$column} IN ($variables)");

        } else {

            array_push($this->whereCollection, "{$column} IN ($variables)");
        }

        // push value as parameters
        foreach ($value as $n => $item):

            array_push($this->preparedParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereNotIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            
            foreach ($value as $n => $item):

                $value[$n] = $this->db->escape($item);

            endforeach;
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR NOT {$column} IN ($variables)");

        } else {

            array_push($this->whereCollection, "NOT {$column} IN ($variables)");
        }

        // push value as parameters
        foreach ($value as $n => $item):

            array_push($this->preparedParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR {$column} IS NULL");

        } else {

            array_push($this->whereCollection, "{$column} IS NULL");
        }

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereNotNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR {$column} IS NOT NULL");

        } else {

            array_push($this->whereCollection, "{$column} IS NOT NULL");
        }

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    protected function setLikeValue(string $value, string $method): string
    {
        // set value based on method
        switch ($method) {
            case 'left':
                $value = "%{$value}";
                break;

            case 'right':
                $value = "{$value}%";
                break;
            
            default:
                $value = "%{$value}%";
                break;
        }

        // return
        return $value;
    }

    //=================================================================================================

    public function whereLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $value  = $this->db->escape($value);
        }

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE {$column} LIKE ? ESCAPE '!'");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND {$column} LIKE ? ESCAPE '!'");

            } else {

                array_push($this->whereCollection, "{$column} LIKE ? ESCAPE '!'");
            }
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function notWhereLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $value  = $this->db->escape($value);
        }

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE NOT {$column} LIKE ? ESCAPE '!'");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND NOT {$column} LIKE ? ESCAPE '!'");

            } else {

                array_push($this->whereCollection, "NOT {$column} LIKE ? ESCAPE '!'");
            }
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $value  = $this->db->escape($value);
        }

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR {$column} LIKE ? ESCAPE '!'");

        } else {
            
            array_push($this->whereCollection, "{$column} LIKE ? ESCAPE '!'");
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orNotWhereLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $value  = $this->db->escape($value);
        }

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR NOT {$column} LIKE ? ESCAPE '!'");

        } else {
            
            array_push($this->whereCollection, "{$column} LIKE ? ESCAPE '!'");
        }

        // push value as parameters
        array_push($this->preparedParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, 'WHERE (');

        } else {

            array_push($this->whereCollection, 'AND (');
        }

        // don't use conjunction on next
        $this->useConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function notGroupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, 'WHERE NOT (');

        } else {

            array_push($this->whereCollection, 'AND NOT (');
        }

        // don't use conjunction on next
        $this->useConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orGroupStart(): MySQLiBuilder
    {
        // push
        array_push($this->whereCollection, 'OR (');

        // don't use conjunction on next
        $this->useConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orNotGroupStart(): MySQLiBuilder
    {
        // push
        array_push($this->whereCollection, 'OR NOT (');

        // don't use conjunction on next
        $this->useConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupEnd(): MySQLiBuilder
    {
        // push
        array_push($this->whereCollection, ')');

        // don't use conjunction on next
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupBy(): MySQLiBuilder
    {
        
    }

    //=================================================================================================

    public function having(): MySQLiBuilder {}
    public function havingNot(): MySQLiBuilder {}
    public function havingIn(): MySQLiBuilder {}
    public function havingNotIn(): MySQLiBuilder {}
    public function havingNull(): MySQLiBuilder {}
    public function havingNotNull(): MySQLiBuilder {}

    public function orHaving():MySQLiBuilder {}
    public function orHavingNot():MySQLiBuilder {}
    public function orHavingIn():MySQLiBuilder {}
    public function orHavingNotIn():MySQLiBuilder {}
    public function orHavingNull():MySQLiBuilder {}
    public function orHavingNotNull():MySQLiBuilder {}

    public function havingLike(): MySQLiBuilder {}
    public function NotHavingLike(): MySQLiBuilder {}
    public function orHavingLike(): MySQLiBuilder {}
    public function orNotHavingLike(): MySQLiBuilder {}

    public function havingGroupStart(): MySQLiBuilder {}
    public function notHavingGroupStart(): MySQLiBuilder {}
    public function orHavingGroupStart(): MySQLiBuilder {}
    public function orNotHavingGroupStart(): MySQLiBuilder {}
    public function havingGroupEnd(): MySQLiBuilder {}
    
    public function orderBy(): MySQLiBuilder {}

    public function offset(): MySQLiBuilder {}
    public function limit(): MySQLiBuilder {}

    public function countAll(): MySQLiBuilder {}
    public function countAllResults(): MySQLiBuilder {}

    //=================================================================================================

    public function get(): MySQLi
    {
        // select string
        $selectString = ($this->distinct) ? "SELECT DISTINCT" : "SELECT";
        
        // column collection
        // and string
        $this->selectCollection = empty($this->selectCollection) ? [ "`{$this->from}`.*" ] : $this->selectCollection;
        $columnString = implode(',', $this->selectCollection);

        // from string
        $fromString = "FROM {$this->from}";

        // join string
        if (!empty($this->joinCollection))
        {
            $joinString = [];

            foreach ($this->joinCollection as $join):

                $joinText  = is_null($join['type']) ? 'JOIN' : "{$this->allowedJoinType[$join['type']]} JOIN";
                array_push($joinString, "{$joinText} {$join['table']} ON {$join['condition']}");

            endforeach;

            $fromString .= " " . implode(" ", $joinString);
        }

        // where string
        $whereString = '';
        
        if (!empty($this->whereCollection))
        {
            $whereString = implode(" ", $this->whereCollection);
        }

        // update result
        $this->preparedQuery = "{$selectString} {$columnString} {$fromString} {$whereString}";

        // dd($this);

        // return
        return $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
    }

    //=================================================================================================

    public function getCompiledSelect() {}

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