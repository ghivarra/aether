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

    public function join(string $table, string|array $condition, string $joinType = '', bool $raw = false): MySQLiBuilder
    {
        if (!empty($joinType))
        {
            if (!in_array(strtolower($joinType), array_keys($this->allowedJoinType)))
            {
                $joinType = esc($joinType);
                throw new SystemException("{$joinType} is not allowed", 500);
            }
        }

        // check if condition is array
        if (is_array($condition))
        {
            $condition[0] = $this->addPrefix($condition[0], $this->db);
            
            // push into params
            if (isset($condition[1]) && !empty($condition[1]))
            {
                foreach ($condition[1] as $param):

                    array_push($this->joinParams, $param);

                endforeach;
            }

        } else {

            $condition = $this->addPrefix($condition, $this->db);
        }

        // sanitize data
        $data = [
            'type'      => empty($joinType) ? null : strtolower($joinType),
            'table'     => ($raw) ? $table : $this->sanitizeTable($table),
            'condition' => is_array($condition) ? $condition[0] : $condition,
        ];

        // push data
        $this->pushCollection('join', $data);

        // return instance
        return $this;
    }

    //=================================================================================================

    public function innerJoin(string $table, string|array $condition, bool $raw = false): MySQLiBuilder
    {
        // return instance
        return $this->join($table, $condition, 'inner', $raw);
    }

    //=================================================================================================

    public function OuterJoin(string $table, string|array $condition, bool $raw = false): MySQLiBuilder
    {
        // return instance
        return $this->join($table, $condition, 'outer', $raw);
    }

    //=================================================================================================

    public function leftJoin(string $table, string|array $condition, bool $raw = false): MySQLiBuilder
    {
        // return instance
        return $this->join($table, $condition, 'left', $raw);
    }

    //=================================================================================================

    public function rightJoin(string $table, string|array $condition, bool $raw = false): MySQLiBuilder
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
            $this->pushCollection('where', "WHERE {$data['column']} {$operator} ?", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$data['column']} {$operator} ?", $value);

            } else {

                $this->pushCollection('where', "{$data['column']} {$operator} ?", $value);
            }
        }

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
            $this->pushCollection('where', "WHERE NOT {$data['column']} {$operator} ?", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND NOT {$data['column']} {$operator} ?", $value);

            } else {

                $this->pushCollection('where', "NOT {$data['column']} {$operator} ?", $value);
            }
        }

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
            $this->pushCollection('where', "WHERE {$column} IN ($variables)", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} IN ($variables)", $value);

            } else {

                $this->pushCollection('where', "{$column} IN ($variables)", $value);
            }            
        }

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
            $this->pushCollection('where', "WHERE {$column} NOT IN ($variables)", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} NOT IN ($variables)", $value);

            } else {

                $this->pushCollection('where', "{$column} NOT IN ($variables)", $value);
            }   
        }

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
            $this->pushCollection('where', "WHERE {$column} IS NULL");

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} IS NULL");

            } else {

                $this->pushCollection('where', "{$column} IS NULL");
            }   
        }

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
            $this->pushCollection('where', "WHERE {$column} IS NOT NULL");

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} IS NOT NULL");

            } else {

                $this->pushCollection('where', "{$column} IS NOT NULL");
            } 
        }

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
            $this->pushCollection('where', "OR {$data['column']} {$operator} ?", $value);

        } else {

            $this->pushCollection('where', "{$data['column']} {$operator} ?", $value);
        }

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
            $this->pushCollection('where', "OR NOT {$data['column']} {$operator} ?", $value);

        } else {

            $this->pushCollection('where', "NOT {$data['column']} {$operator} ?", $value);
        }

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
            $this->pushCollection('where', "OR {$column} IN ($variables)", $value);

        } else {

            $this->pushCollection('where', "{$column} IN ($variables)", $value);
        }

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
            $this->pushCollection('where', "OR NOT {$column} IN ($variables)", $value);

        } else {

            $this->pushCollection('where', "NOT {$column} IN ($variables)", $value);
        }

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
            $this->pushCollection('where', "OR {$column} IS NULL");

        } else {

            $this->pushCollection('where', "{$column} IS NULL");
        }

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
            $this->pushCollection('where', "OR {$column} IS NOT NULL");

        } else {

            $this->pushCollection('where', "{$column} IS NOT NULL");
        }

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
            $this->pushCollection('where', "WHERE {$column} LIKE ? ESCAPE '!'", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} LIKE ? ESCAPE '!'", $value);

            } else {

                $this->pushCollection('where', "{$column} LIKE ? ESCAPE '!'", $value);
            }
        }

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
            $this->pushCollection('where', "WHERE {$column} NOT LIKE ? ESCAPE '!'", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} NOT LIKE ? ESCAPE '!'", $value);

            } else {

                $this->pushCollection('where', "{$column} NOT LIKE ? ESCAPE '!'", $value);
            }
        }

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
            $this->pushCollection('where', "OR {$column} LIKE ? ESCAPE '!'", $value);

        } else {
            
            $this->pushCollection('where', "{$column} LIKE ? ESCAPE '!'", $value);
        }

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
            $this->pushCollection('where', "OR {$column} NOT LIKE ? ESCAPE '!'", $value);

        } else {
            
            $this->pushCollection('where', "{$column} NOT LIKE ? ESCAPE '!'", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->whereCollection))
        {
            $this->pushCollection('groupWhere', 'WHERE (');

        } else {

            $this->pushCollection('groupWhere', 'AND (');
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function notGroupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->whereCollection))
        {
            $this->pushCollection('groupWhere', 'WHERE NOT (');

        } else {

            $this->pushCollection('groupWhere', 'AND NOT (');
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orGroupStart(): MySQLiBuilder
    {
        // push
        $this->pushCollection('groupWhere', 'OR (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orNotGroupStart(): MySQLiBuilder
    {
        // push
        $this->pushCollection('groupWhere', 'OR NOT (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupEnd(): MySQLiBuilder
    {
        // push
        $this->pushCollection('groupWhereEnd', ')');

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
            $this->pushCollection('having', "HAVING {$data['column']} {$operator} ?", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$data['column']} {$operator} ?", $value);

            } else {

                $this->pushCollection('having', "{$data['column']} {$operator} ?", $value);
            }
        }

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
            $this->pushCollection('having', "HAVING NOT {$data['column']} {$operator} ?", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND NOT {$data['column']} {$operator} ?", $value);

            } else {

                $this->pushCollection('having', "NOT {$data['column']} {$operator} ?", $value);
            }
        }

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
            $this->pushCollection('having', "HAVING {$column} IN ($variables)", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} IN ($variables)", $value);

            } else {

                $this->pushCollection('having', "{$column} IN ($variables)", $value);
            }            
        }

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
            $this->pushCollection('having', "HAVING {$column} NOT IN ($variables)", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} NOT IN ($variables)", $value);

            } else {

                $this->pushCollection('having', "{$column} NOT IN ($variables)", $value);
            }            
        }

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
            $this->pushCollection('having', "HAVING {$column} IS NULL");

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} IS NULL");

            } else {

                $this->pushCollection('having', "{$column} IS NULL");
            }   
        }

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
            $this->pushCollection('having', "HAVING {$column} IS NOT NULL");

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} IS NOT NULL");

            } else {

                $this->pushCollection('having', "{$column} IS NOT NULL");
            }   
        }

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
            $this->pushCollection('having', "OR {$data['column']} {$operator} ?", $value);

        } else {

            $this->pushCollection('having', "{$data['column']} {$operator} ?", $value);
        }

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
            $this->pushCollection('having', "OR NOT {$data['column']} {$operator} ?", $value);

        } else {

            $this->pushCollection('having', "NOT {$data['column']} {$operator} ?", $value);
        }

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
            $this->pushCollection('having', "OR {$column} IN ($variables)", $value);

        } else {

            $this->pushCollection('having', "{$column} IN ($variables)", $value);
        }

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
            $this->pushCollection('having', "OR NOT {$column} IN ($variables)", $value);

        } else {

            $this->pushCollection('having', "NOT {$column} IN ($variables)", $value);
        }

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
            $this->pushCollection('having', "OR {$column} IS NULL");

        } else {

            $this->pushCollection('having', "{$column} IS NULL");
        }

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
            $this->pushCollection('having', "OR {$column} IS NOT NULL");

        } else {

            $this->pushCollection('having', "{$column} IS NOT NULL");
        }

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
            $this->pushCollection('having', "HAVING {$column} LIKE ? ESCAPE '!'", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} LIKE ? ESCAPE '!'", $value);

            } else {

                $this->pushCollection('having', "{$column} LIKE ? ESCAPE '!'", $value);
            }
        }

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
            $this->pushCollection('having', "HAVING {$column} NOT LIKE ? ESCAPE '!'", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} NOT LIKE ? ESCAPE '!'", $value);

            } else {

                $this->pushCollection('having', "{$column} NOT LIKE ? ESCAPE '!'", $value);
            }
        }

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
            $this->pushCollection('having', "OR {$column} LIKE ? ESCAPE '!'", $value);

        } else {
            
            $this->pushCollection('having', "{$column} LIKE ? ESCAPE '!'", $value);
        }

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
            $this->pushCollection('having', "OR {$column} NOT LIKE ? ESCAPE '!'", $value);

        } else {
            
            $this->pushCollection('having', "{$column} NOT LIKE ? ESCAPE '!'", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingGroupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->havingCollection))
        {
            $this->pushCollection('groupHaving', 'HAVING (');

        } else {

            $this->pushCollection('groupHaving', 'AND (');
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNotGroupStart(): MySQLiBuilder
    {
        // push
        if (empty($this->havingCollection))
        {
            $this->pushCollection('groupHaving', 'HAVING NOT (');

        } else {

            $this->pushCollection('groupHaving', 'AND NOT (');
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingOrGroupStart(): MySQLiBuilder
    {
        // push
        $this->pushCollection('groupHaving', 'OR (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingOrNotGroupStart(): MySQLiBuilder
    {
        // push
        $this->pushCollection('groupHaving', 'OR NOT (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingGroupEnd(): MySQLiBuilder
    {
        // push
        $this->pushCollection('groupHavingEnd', ')');

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

    protected function buildQuery(string $command): void
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

    public function get(bool $resetQuery = false): MySQLi
    {
        // build query
        $this->buildQuery('select');

        // build params
        $this->buildParams();

        // store variable
        $result = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);

        // reset query
        // if commanded to do it
        if ($resetQuery)
        {
            $this->resetQuery();
        }

        // return
        return $result;
    }

    //=================================================================================================

    protected function compileQueryString(): string
    {
        // move to new variable
        $query  = $this->preparedQuery;
        $params = $this->preparedParams;

        // explode query
        $queryArray = explode(" ", $query);

        // walk array to convert the question mark into params
        foreach ($queryArray as $i => $item):

            if ($item === '?')
            {
                $queryArray[$i] = array_shift($params);
            }

        endforeach;
        
        // return
        return implode(" ", $queryArray);
    }

    //=================================================================================================

    public function getCompiledSelect(bool $resetQuery = false): string
    {
        // build query first
        $this->buildQuery('select');

        // build params
        $this->buildParams();

        // return as string
        $result = $this->compileQueryString();

        // reset query
        // if commanded to do it
        if ($resetQuery)
        {
            $this->resetQuery();
        }

        // return
        return $result;
    }

    //=================================================================================================

    public function resetQuery(): MySQLiBuilder
    {
        // reset
        $this->selectCollection = [];
        $this->distinct = false;
        $this->from = '';
        $this->joinCollection = [];
        $this->joinParams = [];
        $this->whereCollection = [];
        $this->whereParams = [];
        $this->useConjunction = true;
        $this->groupByCollection = [];
        $this->havingCollection = [];
        $this->havingParams = [];
        $this->havingUseConjunction = true;
        $this->orderByCollection = [];
        $this->limitCount = null;
        $this->offsetCount = null;
        $this->setCollection = [];
        $this->setParams = [];
        $this->resultQuery = '';
        $this->preparedQuery = '';
        $this->preparedParams = [];
        $this->prefix = '';
        $this->onSubquery = false;
        $this->subqueries = [];

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