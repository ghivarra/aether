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
    protected array $allowedOrder = [
        'ASC', 'DESC'
    ];

    //=================================================================================================

    protected function sanitizeColumn(string $column): string
    {
        // remove backticks, single-quotes, double-quotes
        $column = str_replace(['`', '"', '"'], '', $column);

        // get
        if (str_contains($column, '.'))
        {
            // explode and sanitize
            $columnArray    = explode('.', $column);
            $columnArray[0] = $this->db->escape($columnArray[0]);
            $columnArray[1] = '`' . $this->db->escape($columnArray[1]) . '`';

            if ($this->prefix !== substr($columnArray[0], 0, strlen($this->prefix)))
            {
                // add prefix
                $columnArray[0] = "`{$this->prefix}{$columnArray[0]}`";

                // implode
                $column = implode('.', $columnArray);

            } else {

                $columnArray[0] = "`{$columnArray[0]}`";
                $column         = implode('.', $columnArray);
            }

        } else {

            // escape column
            $column = '`' . $this->db->escape($column) . '`';
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
        return $this->db->escape($table);
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
            $alias  = '`' . $this->db->escape($alias) . '`';
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
            $alias  = '`' . $this->db->escape($alias) . '`';
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
            $alias  = '`' . $this->db->escape($alias) . '`';
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
            $alias  = '`' . $this->db->escape($alias) . '`';
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
            $alias  = '`' . $this->db->escape($alias) . '`';
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
                throw new SystemException("{$joinType} is not allowed", 500);
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
                    throw new SystemException("{$joinType} as a comparison operator is not allowed", 500);
                }
            }
        }

        // return
        return [
            'column' => ($raw) ? $column : $this->sanitizeColumn($column),
            'value'  => $value,
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
        array_push($this->whereParams, $value);

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
        array_push($this->whereParams, $value);

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
        foreach ($value as $item):

            array_push($this->whereParams, $item);

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
        foreach ($value as $item):

            array_push($this->whereParams, $item);

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
        array_push($this->whereParams, $value);

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
        array_push($this->whereParams, $value);

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
        foreach ($value as $item):

            array_push($this->whereParams, $item);

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
        foreach ($value as $item):

            array_push($this->whereParams, $item);

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
        array_push($this->whereParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function whereNotLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into where collection
        if (empty($this->whereCollection))
        {
            array_push($this->whereCollection, "WHERE {$column} NOT LIKE ? ESCAPE '!'");

        } else {

            if ($this->useConjunction)
            {
                array_push($this->whereCollection, "AND {$column} NOT LIKE ? ESCAPE '!'");

            } else {

                array_push($this->whereCollection, "{$column} NOT LIKE ? ESCAPE '!'");
            }
        }

        // push value as parameters
        array_push($this->whereParams, $value);

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
        }

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR {$column} LIKE ? ESCAPE '!'");

        } else {
            
            array_push($this->whereCollection, "{$column} LIKE ? ESCAPE '!'");
        }

        // push value as parameters
        array_push($this->whereParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->useConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereNotLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into where collection
        if ($this->useConjunction)
        {
            array_push($this->whereCollection, "OR {$column} NOT LIKE ? ESCAPE '!'");

        } else {
            
            array_push($this->whereCollection, "{$column} NOT LIKE ? ESCAPE '!'");
        }

        // push value as parameters
        array_push($this->whereParams, $value);

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

    public function groupBy(string|array $columns, bool $raw = false): MySQLiBuilder
    {
        // sanitize & push to collection
        if (is_array($columns))
        {
            foreach ($columns as $column):

                if (!$raw)
                {
                    $column = $this->sanitizeColumn($column);
                }

                array_push($this->groupByCollection, $column);

            endforeach;

        } else {

            if (!$raw)
            {
                $column = $this->sanitizeColumn($columns);
            }

            array_push($this->groupByCollection, $column);
        }
        
        // return instance
        return $this;
    }

    //=================================================================================================

    public function having(string $column, string $operator, string|int $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING {$data['column']} {$operator} ?");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND {$data['column']} {$operator} ?");

            } else {

                array_push($this->havingCollection, "{$data['column']} {$operator} ?");
            }
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNot(string $column, string $operator, string|int $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING NOT {$data['column']} {$operator} ?");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND NOT {$data['column']} {$operator} ?");

            } else {

                array_push($this->havingCollection, "NOT {$data['column']} {$operator} ?");
            }
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING {$column} IN ($variables)");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND {$column} IN ($variables)");

            } else {

                array_push($this->havingCollection, "{$column} IN ($variables)");
            }            
        }

        // push value as parameters
        foreach ($value as $item):

            array_push($this->havingParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNotIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING {$column} NOT IN ($variables)");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND {$column} NOT IN ($variables)");

            } else {

                array_push($this->havingCollection, "{$column} NOT IN ($variables)");
            }            
        }

        // push value as parameters
        foreach ($value as $item):

            array_push($this->havingParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING {$column} IS NULL");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND {$column} IS NULL");

            } else {

                array_push($this->havingCollection, "{$column} IS NULL");
            }   
        }

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNotNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING {$column} IS NOT NULL");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND {$column} IS NOT NULL");

            } else {

                array_push($this->havingCollection, "{$column} IS NOT NULL");
            }   
        }

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHaving(string $column, string $operator, string $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR {$data['column']} {$operator} ?");

        } else {

            array_push($this->havingCollection, "{$data['column']} {$operator} ?");
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingNot(string $column, string $operator, string $value, bool $raw = false): MySQLiBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR NOT {$data['column']} {$operator} ?");

        } else {

            array_push($this->havingCollection, "NOT {$data['column']} {$operator} ?");
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR {$column} IN ($variables)");

        } else {

            array_push($this->havingCollection, "{$column} IN ($variables)");
        }

        // push value as parameters
        foreach ($value as $item):

            array_push($this->havingParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingNotIn(string $column, array $value, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $variables = implode(', ', array_fill(0, count($value), '?'));

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR NOT {$column} IN ($variables)");

        } else {

            array_push($this->havingCollection, "NOT {$column} IN ($variables)");
        }

        // push value as parameters
        foreach ($value as $item):

            array_push($this->havingParams, $item);

        endforeach;

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR {$column} IS NULL");

        } else {

            array_push($this->havingCollection, "{$column} IS NULL");
        }

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingNotNull(string $column, bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR {$column} IS NOT NULL");

        } else {

            array_push($this->havingCollection, "{$column} IS NOT NULL");
        }

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING {$column} LIKE ? ESCAPE '!'");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND {$column} LIKE ? ESCAPE '!'");

            } else {

                array_push($this->havingCollection, "{$column} LIKE ? ESCAPE '!'");
            }
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNotLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, "HAVING {$column} NOT LIKE ? ESCAPE '!'");

        } else {

            if ($this->havingUseConjunction)
            {
                array_push($this->havingCollection, "AND {$column} NOT LIKE ? ESCAPE '!'");

            } else {

                array_push($this->havingCollection, "{$column} NOT LIKE ? ESCAPE '!'");
            }
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR {$column} LIKE ? ESCAPE '!'");

        } else {
            
            array_push($this->havingCollection, "{$column} LIKE ? ESCAPE '!'");
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingNotLike(string $column, string $value, string $method = 'both', bool $raw = false): MySQLiBuilder
    {
        $value = $this->setLikeValue($value, $method);

        // escape or not
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // push into having collection
        if ($this->havingUseConjunction)
        {
            array_push($this->havingCollection, "OR {$column} NOT LIKE ? ESCAPE '!'");

        } else {
            
            array_push($this->havingCollection, "{$column} NOT LIKE ? ESCAPE '!'");
        }

        // push value as parameters
        array_push($this->havingParams, $value);

        // set conjunction true
        // after every Where Collection push
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingGroupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, 'HAVING (');

        } else {

            array_push($this->havingCollection, 'AND (');
        }

        // don't use conjunction on next
        $this->havingUseConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNotGroupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->havingCollection))
        {
            array_push($this->havingCollection, 'HAVING NOT (');

        } else {

            array_push($this->havingCollection, 'AND NOT (');
        }

        // don't use conjunction on next
        $this->havingUseConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingOrGroupStart(): MySQLiBuilder
    {
        // push
        array_push($this->havingCollection, 'OR (');

        // don't use conjunction on next
        $this->havingUseConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingOrNotGroupStart(): MySQLiBuilder
    {
        // push
        array_push($this->havingCollection, 'OR NOT (');

        // don't use conjunction on next
        $this->havingUseConjunction = false;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingGroupEnd(): MySQLiBuilder
    {
        // push
        array_push($this->havingCollection, ')');

        // don't use conjunction on next
        $this->havingUseConjunction = true;

        // return instance
        return $this;
    }

    //=================================================================================================
    
    public function orderBy(string $column, string $order = 'ASC', bool $raw = false): MySQLiBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        $order = strtoupper($order);
        $order = in_array($order, $this->allowedOrder) ? $order : 'ASC';

        // store order
        array_push($this->orderByCollection, "{$column} {$order}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function offset(int $num): MySQLiBuilder 
    {
        $this->offsetCount = $num;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function limit(int $num): MySQLiBuilder
    {
        $this->limitCount = $num;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function buildParams(): void
    {
        // reset params
        $this->preparedParams = [];

        // sort and add params
        $this->preparedParams = array_merge($this->joinParams, $this->whereParams, $this->havingParams);
    }

    //=================================================================================================

    public function buildQuery(string $command): void
    {
        // reset prepared
        $this->preparedQuery = '';

        // set command
        if ($command === 'select')
        {
            // select string
            $selectString = ($this->distinct) ? "SELECT DISTINCT" : "SELECT";

            // store into prepared query
            $this->preparedQuery = $selectString;
            
            // column collection
            // and string
            $this->selectCollection = empty($this->selectCollection) ? [ "`{$this->from}`.*" ] : $this->selectCollection;
            $columnString = implode(', ', $this->selectCollection);

            // store into prepared query
            $this->preparedQuery .= " {$columnString}";

        } elseif ($command === 'count') {

            // store into prepared query
            $this->preparedQuery = "SELECT COUNT(*) AS total";
        }

        // from string
        $fromString = "FROM `{$this->from}`";

        // store into prepared query
        $this->preparedQuery .= " {$fromString}";

        // join string
        if (!empty($this->joinCollection))
        {
            $joinString = [];

            foreach ($this->joinCollection as $join):

                $joinText  = is_null($join['type']) ? 'JOIN' : "{$this->allowedJoinType[$join['type']]} JOIN";
                array_push($joinString, "{$joinText} {$join['table']} ON {$join['condition']}");

            endforeach;

            // store into prepared query
            $this->preparedQuery .= " " . implode(" ", $joinString);
        }

        // where string        
        if (!empty($this->whereCollection))
        {
            $whereString = implode(" ", $this->whereCollection);

            // store into prepared query
            $this->preparedQuery .= " {$whereString}";
        }

        // group by string
        if (!empty($this->groupByCollection))
        {
            $groupByString = "GROUP BY " . implode(", ", $this->groupByCollection);

            // store into prepared query
            $this->preparedQuery .= " {$groupByString}";
        }

        // having string        
        if (!empty($this->havingCollection))
        {
            $havingString = implode(" ", $this->havingCollection);

            // store into prepared query
            $this->preparedQuery .= " {$havingString}";
        }

        // order by
        if (!empty($this->orderByCollection))
        {
            $orderByString = 'ORDER BY ' . implode(", ", $this->orderByCollection);

            // store into prepared query
            $this->preparedQuery .= " {$orderByString}";
        }

        //  limit
        if (!is_null($this->limitCount))
        {
            // store into prepared query
            $this->preparedQuery .= " LIMIT {$this->limitCount}";
        }

        // offset
        if (!is_null($this->offsetCount))
        {
            // store into prepared query
            $this->preparedQuery .= " OFFSET {$this->offsetCount}";
        }
    }

    //=================================================================================================

    public function countAll(): int
    {
        $query  = "SELECT COUNT(*) AS total FROM `{$this->from}`";
        $result = $this->db->rawQuery($query)->getRowArray();

        // return
        return intval($result['total']);
    }

    //=================================================================================================

    public function countAllResults(): int
    {
        // build
        $this->buildQuery('count');

        // query
        $query  = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        $result = $query->getRowArray();

        // return
        return intval($result['total']);
    }

    //=================================================================================================

    public function get(): MySQLi
    {
        // build query
        $this->buildQuery('select');

        // build params
        $this->buildParams();

        // dd($this);

        // return
        return $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
    }

    //=================================================================================================

    protected function putQueryParams(): string
    {
        $query = $this->preparedQuery;

        
        return '';
    }

    //=================================================================================================

    public function getCompiledSelect(): string
    {
        return $this->putQueryParams();
    }

    //=================================================================================================

    public function resetQuery(): MySQLiBuilder
    {
        // return instance
        return $this;
    }

    //=================================================================================================

    public function set(): MySQLiBuilder
    {
        // return instance
        return $this;
    }

    //=================================================================================================

    public function increment(): MySQLiBuilder
    {
        // return instance
        return $this;
    }

    //=================================================================================================

    public function decrement(): MySQLiBuilder
    {
        // return instance
        return $this;
    }

    //=================================================================================================

    public function insert(): bool
    {
        return true;
    }

    //=================================================================================================

    public function insertBatch(): bool
    {
        return true;
    }

    //=================================================================================================

    public function update(): bool
    {
        return true;
    }

    //=================================================================================================

    public function updateBatch(): bool
    {
        return true;
    }

    //=================================================================================================

    public function delete(): bool
    {
        return true;
    }

    //=================================================================================================

    public function replace(): bool
    {
        return true;
    }

    //=================================================================================================

    public function truncate(): bool
    {
        return true;
    }

    //=================================================================================================

    public function emptyTable(): bool
    {
        return true;
    }

    //=================================================================================================
}