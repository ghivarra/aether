<?php 

declare(strict_types = 1);

namespace Aether\Database\Builder;

use Aether\Database\Builder;
use Aether\Database\BaseBuilderTrait;
use Aether\Database\Driver\MySQLi;
use Aether\Exception\SystemException;
use Aether\Database\DriverInterface;

class MySQLiBuilder extends Builder
{
    use BaseBuilderTrait;

    protected MySQLi|null $db = null;
    protected bool $allowTruncate = true;
    protected int $bulkLimit = 100;
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

    public function buildGetParams(): void
    {
        // reset params
        $this->preparedParams = [];

        // sort and add params
        $this->preparedParams = array_merge($this->joinParams, $this->whereParams, $this->havingParams);
    }

    //=================================================================================================

    protected function buildGetQuery(string $command): void
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

    public function compilePreparedQuery(string $query = '', array $params = []): string
    {
        // move to new variable
        $query    = empty($query) ? $this->preparedQuery : $query;
        $params   = empty($params) ? $this->preparedParams : $params;
        $variable = [
            '(?,', '?,', '?),', '?)', '?'
        ];

        // explode query
        $queryArray = explode(" ", $query);

        // walk array to convert the question mark into params
        foreach ($queryArray as $i => $item):

            if (in_array($item, $variable))
            {
                $key   = array_search($item, $variable);
                $param = "'" . $this->db->escape(array_shift($params)) . "'";
                $value = str_replace('?', $param, $variable[$key]);

                // store
                $queryArray[$i] = $value;
            }

        endforeach;
        
        // return
        return implode(" ", $queryArray);
    }

    //=================================================================================================

    public function countAll(bool $resetQuery = true): int
    {
        $query  = "SELECT COUNT(*) AS total FROM `{$this->from}`";
        $result = $this->db->rawQuery($query)->getRowArray();

        // reset query
        // if commanded to do it
        if ($resetQuery)
        {
            $this->resetQuery();
        }

        // return
        return intval($result['total']);
    }

    //=================================================================================================

    public function countAllResults(bool $resetQuery = true): int
    {
        // build query
        $this->buildGetQuery('count');

        // build params
        $this->buildGetParams();

        // query
        $query  = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        $result = $query->getRowArray();

        // reset query
        // if commanded to do it
        if ($resetQuery)
        {
            $this->resetQuery();
        }

        // return
        return intval($result['total']);
    }

    //=================================================================================================

    public function get(bool $resetQuery = true): MySQLi
    {
        // build query
        $this->buildGetQuery('select');

        // build params
        $this->buildGetParams();

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

    public function getCompiledSelect(bool $resetQuery = true): string
    {
        // build query first
        $this->buildGetQuery('select');

        // build params
        $this->buildGetParams();

        // return as string
        $result = $this->compilePreparedQuery();

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
        // select
        $this->selectCollection = [];
        $this->distinct = false;
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
        $this->resultQuery = '';
        $this->preparedQuery = '';
        $this->preparedParams = [];

        // subquery
        $this->onSubquery = false;
        $this->subqueries = [];

        // create-update
        $this->setDataParams = [];
        $this->setDataCollection = [
            'key'   => [],
            'value' => [],
        ];

        // create-update batch
        $this->setColumnBatch = '';
        $this->setDataBatchParams = [];
        $this->setDataBatchCollection = [
            'key'   => [],
            'value' => [],
        ];

        // replace
        $this->setReplaceParams = [];
        $this->setReplaceCollection = [];

        // return instance
        return $this;
    }

    //=================================================================================================

    public function set(string|array $data, string|int|null|bool $value = false): MySQLiBuilder
    {
        // push data
        $this->pushSetData($data, $value);

        // return instance
        return $this;
    }

    //=================================================================================================

    public function setReplace(string|array $data, string|int|null $oldValue = '', string|int|null $newValue = '', bool $raw = false): MySQLiBuilder
    {
        if (is_array($data))
        {
            foreach ($data as $item):

                $raw = isset($item[3]) ? $item[3] : false;
                $this->setReplace($item[0], $item[1], $item[2], $raw);

            endforeach;

            // return
            return $this;
        }

        // always sanitize
        // no point in not sanitizing
        $column = $this->sanitizeColumn($data);

        // push column
        array_push($this->setReplaceCollection, [
            'column'   => $column,
            'oldValue' => ($raw) ? $oldValue : '?',
            'newValue' => ($raw) ? $newValue : '?',
        ]);

        // push into params if not raw
        if (!$raw)
        {
            array_push($this->setReplaceParams, $oldValue);
            array_push($this->setReplaceParams, $newValue);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function increment(string $column, int $incNum = 1, bool $raw = false): MySQLiBuilder
    {
        $column = ($raw) ? $column : $this->sanitizeColumn($column);

        // set
        $this->pushSetData($column, "{$column}+{$incNum}", false);

        // return instance
        return $this;
    }

    //=================================================================================================

    public function decrement(string $column, int $incNum = 1, bool $raw = false): MySQLiBuilder
    {
        $column = ($raw) ? $column : $this->sanitizeColumn($column);

        // set
        $this->pushSetData($column, "{$column}+{$incNum}", false);

        // return instance
        return $this;
    }

    //=================================================================================================

    protected function buildSetQuery(string $type = 'insert'): void
    {
        // reset prepared statement
        $this->preparedQuery = '';

        // build string
        switch ($type) {
            case 'insert':
                $columns = implode(', ', $this->setDataCollection['key']);
                $values  = implode(', ', $this->setDataCollection['value']);
                $this->preparedQuery = "INSERT INTO `{$this->from}` ({$columns}) VALUES ({$values})";
                break;

            case 'insertBulk':
                $values  = [];
                $columns = implode(', ', $this->setDataBatchCollection['key']);

                foreach ($this->setDataBatchCollection['value'] as $value):

                    array_push($values, '('. implode(', ', $value) .')');

                endforeach;

                $values = implode(', ', $values);
                $this->preparedQuery = "INSERT INTO `{$this->from}` ({$columns}) VALUES {$values}";
                break;

            case 'update':  
                $sets = [];

                foreach ($this->setDataCollection['key'] as $i => $column):

                    $column = $this->sanitizeColumn($column);
                    $value  = $this->setDataCollection['value'][$i];
                    array_push($sets, "{$column} = {$value}");

                endforeach;

                // sets
                $columns = implode(', ', $sets);

                // update query
                $this->preparedQuery = "UPDATE `{$this->from}` SET {$columns}";

                // where string        
                if (!empty($this->whereCollection))
                {
                    $whereString = implode(" ", $this->whereCollection);

                    // store into prepared query
                    $this->preparedQuery .= " {$whereString}";
                }
                break;

            case 'updateBulk':

                // show columns data
                $tableColumns = $this->db->rawQuery("SHOW COLUMNS FROM `{$this->from}`")->getResultArray();
                $keys         = $this->setDataBatchCollection['key'];
                $tableDataCol = array_column($tableColumns, 'Field');
                $tempTables   = [];

                // handle columns
                foreach ($keys as $key):
                    
                    $tmp = str_ireplace('`', '', $key);
                    $num = array_search($tmp, $tableDataCol);

                    if ($num === FALSE)
                    {
                        $message = (AETHER_ENV === 'development') ? "Column {$key} is not found in table `{$this->from}`" : 'Failed to update data';
                        throw new SystemException($message, 500);
                    }

                    $columnData = $tableColumns[$num];
                    $null       = ($columnData['Null'] == 'NO') ? 'NOT NULL' : 'NULL';
                    $type       = strtoupper($columnData['Type']);

                    // push
                    array_push($tempTables, "{$columnData['Field']} {$type} {$null}");

                endforeach;

                // make create table script
                $tempTables    = implode(', ', $tempTables);
                $tempTableName = 'tmp_' . time() . '_' . random_int(10000, 99999);

                // run temp table query
                $this->db->rawQuery("CREATE TEMPORARY TABLE {$tempTableName} ({$tempTables})");

                // temporary insert bulk
                $values  = [];
                $columns = implode(', ', $this->setDataBatchCollection['key']);

                foreach ($this->setDataBatchCollection['value'] as $value):

                    array_push($values, '('. implode(', ', $value) .')');

                endforeach;

                // insert bulk into temp table using prepared statement
                $this->db->preparedQuery("INSERT INTO `{$tempTableName}` ({$columns}) VALUES " . implode(', ', $values), $this->setDataBatchParams);

                // create join script
                $mainTableAlias = 'main';
                $tempTableAlias = 'tmp';
                $updateSets     = [];

                foreach ($keys as $updatedKey):

                    // don't update the key
                    // it is the same anyway
                    // make it more efficient
                    if ($updatedKey !== $this->setColumnBatch)
                    {
                        array_push($updateSets, "`{$mainTableAlias}`.{$updatedKey} = `{$tempTableAlias}`.{$updatedKey}");
                    }

                endforeach;

                $this->preparedQuery = "UPDATE `{$this->from}` AS `{$mainTableAlias}` JOIN `{$tempTableName}` AS `{$tempTableAlias}` ON `{$mainTableAlias}`.{$this->setColumnBatch} = `{$tempTableAlias}`.{$this->setColumnBatch} SET " . implode(", ", $updateSets);
                break;

            case 'upsert':
                $keys         = implode(", ", $this->setDataCollection['key']);
                $placeholders = implode(", ", $this->setDataCollection['value']);
                $sets         = [];

                foreach ($this->setDataCollection['key'] as $column):

                    if ($this->sanitizeColumn($column) !== $this->setColumnBatch && !in_array($column, $this->setExcludedColumns))
                    {
                        $column = $this->sanitizeColumn($column);
                        array_push($sets, "{$column} = VALUES({$column})");
                    }
                    
                endforeach;

                $this->preparedQuery = "INSERT INTO `{$this->from}` ($keys) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE ". implode(", ", $sets);
                break;

            case 'upsertBulk':
                $keys = implode(", ", $this->setDataBatchCollection['key']);
                $sets = [];

                foreach ($this->setDataBatchCollection['key'] as $column):

                    if ($this->sanitizeColumn($column) !== $this->setColumnBatch && !in_array($column, $this->setExcludedColumns))
                    {
                        array_push($sets, "{$column} = VALUES({$column})");
                    }
                    
                endforeach;
                
                $placeholders = [];

                foreach ($this->setDataBatchCollection['value'] as $values):

                    array_push($placeholders, "(" . implode(", ", $values) . ")");

                endforeach;

                $placeholders = implode(", ", $placeholders);

                $this->preparedQuery = "INSERT INTO `{$this->from}` ($keys) VALUES {$placeholders} ON DUPLICATE KEY UPDATE ". implode(", ", $sets);
                break;

            case 'delete':
                $this->preparedQuery = "DELETE FROM `{$this->from}`";

                // where string        
                if (!empty($this->whereCollection))
                {
                    $whereString = implode(" ", $this->whereCollection);

                    // store into prepared query
                    $this->preparedQuery .= " {$whereString}";
                }
                break;

            case 'replace':
                $setReplace = [];

                // generate replace query
                foreach ($this->setReplaceCollection as $item):

                    array_push($setReplace, "{$item['column']} = REPLACE({$item['column']}, {$item['oldValue']}, {$item['newValue']})");

                endforeach;

                // set replace
                $setReplace = implode(', ', $setReplace);

                // update query
                $this->preparedQuery = "UPDATE `{$this->from}` SET {$setReplace}";
                break;
            
            default:
                return;
                break;
        }
    }

    //=================================================================================================

    protected function buildSetParams(string $type = 'insert'): void
    {
        // reset prepared params
        $this->preparedParams = [];

        // build string
        switch ($type) {
            case 'insert':
                $this->preparedParams = $this->setDataParams;
                break;

            case 'insertBulk':
                $this->preparedParams = $this->setDataBatchParams;
                break;

            case 'update':
                $this->preparedParams = array_merge($this->setDataParams, $this->whereParams);
                break;

            case 'updateBulk':
                // do nothing
                break;

            case 'upsert':
                $this->preparedParams = $this->setDataParams;
                break;

            case 'upsertBulk':
                $this->preparedParams = $this->setDataBatchParams;
                break;

            case 'delete':
                $this->preparedParams = $this->whereParams;
                break;

            case 'replace':
                $this->preparedParams = $this->setReplaceParams;
                break;
            
            default:
                return;
                break;
        }
    }

    //=================================================================================================

    public function insert(array $data = []): array
    {
        // set data if data not empty
        if (!empty($data))
        {
            $this->set($data);
        }

        // build query
        $this->buildSetQuery('insert');

        // build set params
        $this->buildSetParams('insert');

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // get current connection
        $conn = $db->getCurrentInstance();

        // reset query
        $this->resetQuery();

        // return
        return [
            'status'   => $db->getResult(),
            'insertID' => $conn->insert_id
        ];
    }

    //=================================================================================================

    public function insertBulk(array $data): bool
    {
        // divide by 500 if more than 500
        $total = count($data);

        if ($total > $this->bulkLimit)
        {
            $count  = ceil($total / $this->bulkLimit) - 1;
            $divide = range(0, intval($count));
            $num    = 1;
            
            foreach ($divide as $iteration):

                $start    = $iteration * $this->bulkLimit;
                $dataPart = array_slice($data, $start, $this->bulkLimit);

                // add insert bulk
                $this->insertBulk($dataPart);

                $num++;

            endforeach;

            // conn
            $result = $this->db->getResult();

        } else {

            // push data
            $this->pushSetDataBatch($data);
    
            // build
            $this->buildSetQuery('insertBulk');
    
            // build
            $this->buildSetParams('insertBulk');
    
            // insert bulk start
            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);

            // conn
            $result = $db->getResult();
        }

        // reset query
        $this->resetQuery();

        // return
        return ($result === true);
    }

    //=================================================================================================

    public function update(array $data): array
    {
        // set data if data not empty
        if (!empty($data))
        {
            $this->set($data);
        }

        // build query
        $this->buildSetQuery('update');

        // build set params
        $this->buildSetParams('update');

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // get current connection
        $conn = $db->getCurrentInstance();

        // reset query
        $this->resetQuery();

        // return
        return [
            'status'        => $db->getResult(),
            'affected_rows' => $conn->affected_rows,
        ];
    }

    //=================================================================================================

    public function updateBulk(array $data, string $targetColumn): bool
    {
        // divide by 500 if more than 500
        $total = count($data);

        if ($total > $this->bulkLimit)
        {
            $count  = ceil($total / $this->bulkLimit) - 1;
            $divide = range(0, intval($count));
            $num    = 1;
            
            foreach ($divide as $iteration):

                $start    = $iteration * $this->bulkLimit;
                $dataPart = array_slice($data, $start, $this->bulkLimit);

                // add update bulk
                $this->updateBulk($dataPart, $targetColumn);

                $num++;

            endforeach;

            // conn
            $result = $this->db->getResult();

        } else {
            
            // set table column batch
            $this->setColumnBatch = $this->sanitizeColumn($targetColumn);
    
            // push data
            $this->pushSetDataBatch($data);
    
            // build
            $this->buildSetQuery('updateBulk');
    
            // build
            $this->buildSetParams('updateBulk');
    
            // insert bulk start
            $db = $this->db->rawQuery($this->preparedQuery);
    
            // conn
            $result = $db->getResult();
        }

        // reset query
        $this->resetQuery();

        // return
        return ($result === true);
    }

    //=================================================================================================

    public function upsert(array $data, string $targetColumn = 'id', array $excludedColumns = []): bool
    {
        // set table column batch
        $this->setColumnBatch     = $this->sanitizeColumn($targetColumn);
        $this->setExcludedColumns = $excludedColumns;

        // push data
        $this->pushSetData($data, false);

        // build
        $this->buildSetQuery('upsert');

        // build
        $this->buildSetParams('upsert');

        // insert bulk start
        $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);

        // conn
        $result = $db->getResult();
        
        // return
        return ($result === true);
    }

    //=================================================================================================

    public function upsertBulk(array $data, string $targetColumn = 'id', array $excludedColumns = []): bool
    {
        // divide by 500 if more than 500
        $total = count($data);

        if ($total > $this->bulkLimit)
        {
            $count  = ceil($total / $this->bulkLimit) - 1;
            $divide = range(0, intval($count));
            $num    = 1;
            
            foreach ($divide as $iteration):

                $start    = $iteration * $this->bulkLimit;
                $dataPart = array_slice($data, $start, $this->bulkLimit);

                // add insert bulk
                $this->upsertBulk($dataPart, $targetColumn);

                $num++;

            endforeach;

            // conn
            $result = $this->db->getResult();
            
        } else {

            // set table column batch
            $this->setColumnBatch     = $this->sanitizeColumn($targetColumn);
            $this->setExcludedColumns = $excludedColumns;

            // push data
            $this->pushSetDataBatch($data);

            // build
            $this->buildSetQuery('upsertBulk');

            // build
            $this->buildSetParams('upsertBulk');

            // insert bulk start
            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);

            // conn
            $result = $db->getResult();
        }

        // return
        return ($result === true);
    }

    //=================================================================================================

    public function delete(): array
    {
        // build query
        $this->buildSetQuery('delete');

        // build set params
        $this->buildSetParams('delete');

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // get current connection
        $conn = $db->getCurrentInstance();

        // reset query
        $this->resetQuery();

        // return
        return [
            'status'        => $db->getResult(),
            'affected_rows' => $conn->affected_rows,
        ];
    }

    //=================================================================================================

    public function replace(array $data = []): array
    {
        if (!empty($data))
        {
            foreach ($data as $item):

                $raw = isset($item[3]) ? $item[3] : false;
                $this->setReplace($item[0], $item[1], $item[2], $raw);

            endforeach;
        }

        // set query
        $this->buildSetQuery('replace');

        // set params
        $this->buildSetParams('replace');

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // get current connection
        $conn = $db->getCurrentInstance();

        // reset query
        $this->resetQuery();

        // return
        return [
            'status'        => $db->getResult(),
            'affected_rows' => $conn->affected_rows,
        ];
    }

    //=================================================================================================

    public function truncate(): bool
    {
        if (!$this->allowTruncate)
        {
            return $this->emptyTable();
        }

        // return db
        $db = $this->db->rawQuery("TRUNCATE TABLE `{$this->from}`");

        // conn
        $result = $db->getResult();

        // reset query
        $this->resetQuery();

        // return
        return ($result === true);
    }

    //=================================================================================================

    public function emptyTable(): bool
    {
        // return db
        $db = $this->db->rawQuery("DELETE FROM `{$this->from}`");

        // conn
        $result = $db->getResult();

        // reset query
        $this->resetQuery();

        // return
        return ($result === true);
    }

    //=================================================================================================

    protected function addPrefix(string $input, DriverInterface $db): string
    {
        // explode
        $inputArray = explode(" ", $input);

        // done
        foreach ($inputArray as $i => $inputItem):

            if (str_contains($inputItem, '.'))
            {
                // remove backticks, single-quotes, double-quotes
                $column = str_replace(['`', '"', '"'], '', $inputItem);

                // explode and sanitize
                $columnArray    = explode('.', $column);
                $columnArray[0] = $db->escape($columnArray[0]);
                $columnArray[1] = '`' . $db->escape($columnArray[1]) . '`';

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

                $column = $inputItem;
            }

            // set
            $inputArray[$i] = $column;

        endforeach;

        // return
        return implode(" ", $inputArray);

        // ALL the script below bring too much bug and problems, not gonna use this any longer
        // AI IS GREAT, BUT GENERATING A PERFECT SCRIPT STILL NOT THERE YET

        // Generated this script 95% using OpenAI, It helped me so much as I was totally clueless
        // about regex pattern, I know regex is so powerful and what it can do but still...

        // If ignoring value and only lookup table name

        //        return preg_replace_callback(
        //            '/(?:(\b[a-zA-Z_][a-zA-Z0-9_]*)\.)?([a-zA-Z_][a-zA-Z0-9_]*)/',
        //            function ($matches) use ($prefix, $contextTable) {
        //                $table = $matches[1] ?? null;
        //                $column = $matches[2];
        //
        //                if ($table) {
        //                    return "`{$prefix}{$table}`.$column";
        //                } else {
        //                    // No table specified: assume it's the context table
        //                    return "`{$contextTable}`.$column";
        //                }
        //            },
        //            $input
        //        );

        // $pattern      = '/(?:(\b[a-zA-Z_][a-zA-Z0-9_]*)\.)?([a-zA-Z_][a-zA-Z0-9_]*)/';
        // $result       = '';
        // $offset       = 0;
        // $prefix       = $this->prefix;
        // $contextTable = $this->from;

        // Match quoted strings to skip them
        // preg_match_all('/(["\'])(?:\\\\.|(?!\1).)*\1/', $input, $quotedMatches, PREG_OFFSET_CAPTURE);

        //        foreach ($quotedMatches[0] as $match):
        //
        //            $quoteStart = $match[1];
        //            $quoteEnd = $quoteStart + strlen($match[0]);
        //
        //            // Process content before the quoted string
        //            $before = substr($input, $offset, $quoteStart - $offset);
        //            $before = preg_replace_callback($pattern, function ($matches) use ($contextTable, $prefix) {
        //                $table = $matches[1] ?? null;
        //                $column = $matches[2];
        //
        //                if ($table)
        //                {
        //                    return "`{$prefix}{$table}`.$column";
        //
        //                } else {
        //
        //                    return "`{$contextTable}`.$column";
        //                }
        //
        //            }, $before);
        //
        //            $result .= $before;
        //
        //            // Append the quoted string untouched
        //            $result .= $match[0];
        //            $offset = $quoteEnd;
        //
        //        endforeach;
        //
        //        // Process remaining string after last quote
        //        if ($offset < strlen($input))
        //        {
        //            $rest = substr($input, $offset);
        //            $rest = preg_replace_callback($pattern, function ($matches) use ($contextTable, $prefix) {
        //                $table = $matches[1] ?? null;
        //                $column = $matches[2];
        //
        //                if ($table)
        //                {
        //                    return "`{$prefix}{$table}`.$column";
        //
        //                } else {
        //                    
        //                    return "`{$contextTable}`.$column";
        //                }
        //
        //            }, $rest);
        //
        //            $result .= $rest;
        //        }
        //
        //        // return
        //        return $result;
    }

    //==================================================================================================

    /** 
     * Control on where to push collections, what to run before or after pushing, about conjunctions etc.
     * 
     * @param 'join'|'where'|'having'|'groupWhere'|'groupWhereEnd'|'groupHaving'|'groupHavingEnd' $type
     * 
     * @return void
    **/
    protected function pushCollection(string $type = 'where', string|array $data = '', string|int|bool|float|null|array $params = null): void
    {
        switch ($type) {
            case 'join':
                $collectionVarName = 'joinCollection';
                $paramsVarName = 'joinParams';
                break;

            case 'where':
                $collectionVarName = 'whereCollection';
                $paramsVarName = 'whereParams';
                break;
                
            case 'groupWhere':
                $collectionVarName = 'whereCollection';
                $paramsVarName = 'whereParams';
                break;

            case 'groupWhereEnd':
                $collectionVarName = 'whereCollection';
                $paramsVarName = 'whereParams';
                break;

            case 'having':
                $collectionVarName = 'havingCollection';
                $paramsVarName = 'havingParams';
                break;

            case 'groupHaving':
                $collectionVarName = 'havingCollection';
                $paramsVarName = 'havingParams';
                break;

            case 'groupHavingEnd':
                $collectionVarName = 'havingCollection';
                $paramsVarName = 'havingParams';
                break;
            
            default:
                return;
                break;
        }

        // push into collection
        array_push($this->$collectionVarName, $data);

        // push params if exist
        if (!is_null($params))
        {
            if (is_array($params))
            {
                foreach ($params as $param):

                    array_push($this->$paramsVarName, $param);

                endforeach;

            } else {

                array_push($this->$paramsVarName, $params);
            }
        }

        // after push into params
        switch ($type) {
            case 'join':
                // do nothing
                break;

            case 'where':
                $this->useConjunction = true;
                break;
                
            case 'groupWhere':
                $this->useConjunction = false;
                break;

            case 'groupWhereEnd':
                $this->useConjunction = true;
                break;

            case 'having':
                $this->havingUseConjunction = true;
                break;

            case 'groupHaving':
                $this->havingUseConjunction = false;
                break;

            case 'groupHavingEnd':
                $this->havingUseConjunction = true;
                break;
            
            default:
                // do nothing
                break;
        }
    }

    //==================================================================================================

    /** 
     * Control on where to push set data, what to run before or after pushing
     * 
     * @param string|array $data
     * 
     * @return void
    **/
    protected function pushSetData(string|array $data, string|int|null|bool $value, bool $prepared = true): void
    {
        if (is_array($data) && !$value)
        {
            foreach ($data as $key => $item):

                // use raw value
                $preparedValue = ($prepared) ? '?' : $item;

                // push
                array_push($this->setDataCollection['key'], $key);
                array_push($this->setDataCollection['value'], $preparedValue);

                // update prepared value
                if ($prepared)
                {
                    array_push($this->setDataParams, $item);
                }

            endforeach;

        } else {

            // use raw value
            $preparedValue = ($prepared) ? '?' : $value;

            // push
            array_push($this->setDataCollection['key'], $data);
            array_push($this->setDataCollection['value'], $preparedValue);

            // update prepared value
            if ($prepared)
            {
                array_push($this->setDataParams, $value);
            }
        }
    }

    //==================================================================================================

    protected function pushSetDataBatch(array $data): void
    {
        // push keys
        $columns = array_keys($data[0]);

        foreach ($columns as $i => $column):

            $columns[$i] = $this->sanitizeColumn($column);

        endforeach;

        // set keys
        $this->setDataBatchCollection['key'] = $columns;

        // push data
        foreach ($data as $item):

            // set key/value
            $values = [];

            // iterate
            foreach ($item as $value):
                
                // push value
                array_push($values, '?');

                // push into params
                array_push($this->setDataBatchParams, $value);

            endforeach;

            // set update
            array_push($this->setDataBatchCollection['value'], $values);

        endforeach;
    }

    //==================================================================================================
}