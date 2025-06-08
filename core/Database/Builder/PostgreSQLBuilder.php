<?php 

declare(strict_types = 1);

namespace Aether\Database\Builder;

use Aether\Database\Builder;
use Aether\Database\BaseBuilderTrait;
use Aether\Database\Driver\PostgreSQL;
use Aether\Exception\SystemException;

class PostgreSQLBuilder extends Builder
{
    use BaseBuilderTrait;

    protected string $placeholder = '%%$$$$%%';
    protected PostgreSQL|null $db = null;
    protected bool $allowTruncate = true;
    protected int $bulkLimit = 100;
    protected array $allowedJoinType = [
        'inner' => 'INNER',
        'left'  => 'LEFT',
        'right' => 'RIGHT',
        'full'  => 'FULL',
        'cross' => 'CROSS',
    ];
    protected array $allowedComparisonOperator = [
        '=', '!=', '>', '<', '<=', '>=', '<>'
    ];
    protected array $allowedOrder = [
        'ASC', 'DESC'
    ];

    //=================================================================================================

    protected function seedPlaceholder(): void
    {
        $n          = 1;
        $len        = strlen($this->placeholder);
        $finalQuery = $this->preparedQuery;

        foreach ($this->preparedParams as $param):

            $pos = strpos($finalQuery, $this->placeholder);

            if ($pos === FALSE)
            {
                break;
            }

            $finalQuery = substr_replace($finalQuery, '$' . $n, $pos, $len);
            $n++;

        endforeach;

        // set prepared query
        $this->preparedQuery = $finalQuery;
    }

    //=================================================================================================

    protected function generatePlaceholder(int $start, int $end): array
    {
        return array_map(fn($i) => '$'. $i, range($start, $end));
    }

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
            $columnArray[1] = $this->db->escape($columnArray[1], 'identifier');

            if ($this->prefix !== substr($columnArray[0], 0, strlen($this->prefix)))
            {
                // add prefix
                $columnArray[0] = $this->db->escape($this->prefix . $columnArray[0], 'identifier');

                // implode
                $column = implode('.', $columnArray);

            } else {

                $columnArray[0] = $this->db->escape($columnArray[0], 'identifier');
                $column         = implode('.', $columnArray);
            }

        } else {

            // escape column
            $column = $this->db->escape($column, 'identifier');
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
        return $this->db->escape($table, 'string');
    }

    //=================================================================================================

    public function select(array $columns = [], bool $raw = false): PostgreSQLBuilder
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
    
    public function selectAvg(string $column, string $alias, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias, 'identifier');
        }

        array_push($this->selectCollection, "AVG({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectCount(string $column, string $alias, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias, 'identifier');
        }

        array_push($this->selectCollection, "COUNT({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectMax(string $column, string $alias, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias, 'identifier');
        }

        array_push($this->selectCollection, "MAX({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectMin(string $column, string $alias, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias, 'identifier');
        }

        array_push($this->selectCollection, "MIN({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function selectSum(string $column, string $alias, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
            $alias  = $this->db->escape($alias, 'identifier');
        }

        array_push($this->selectCollection, "SUM({$column}) AS {$alias}");

        // return instance
        return $this;
    }

    //=================================================================================================

    public function distinct(): PostgreSQLBuilder
    {
        $this->distinct = true;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function from(string $tableName, mixed $db, string $DBPrefix): PostgreSQLBuilder
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

    public function join(string $table, string|array $condition, string $joinType = '', bool $raw = false): PostgreSQLBuilder
    {
        if ($joinType === '')
        {
            $joinType = 'inner';
        }

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

    public function innerJoin(string $table, string|array $condition, bool $raw = false): PostgreSQLBuilder
    {
        // return instance
        return $this->join($table, $condition, 'inner', $raw);
    }

    //=================================================================================================

    public function OuterJoin(string $table, string|array $condition, bool $raw = false): PostgreSQLBuilder
    {
        // return instance
        return $this->join($table, $condition, 'outer', $raw);
    }

    //=================================================================================================

    public function leftJoin(string $table, string|array $condition, bool $raw = false): PostgreSQLBuilder
    {
        // return instance
        return $this->join($table, $condition, 'left', $raw);
    }

    //=================================================================================================

    public function rightJoin(string $table, string|array $condition, bool $raw = false): PostgreSQLBuilder
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

    public function where(string $column, string $operator, string|int $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if (empty($this->whereCollection))
        {
            $this->pushCollection('where', "WHERE {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$data['column']} {$operator} {$this->placeholder}", $value);

            } else {

                $this->pushCollection('where', "{$data['column']} {$operator} {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================
    
    public function whereNot(string $column, string $operator, string|int $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if (empty($this->whereCollection))
        {
            $this->pushCollection('where', "WHERE NOT {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND NOT {$data['column']} {$operator} {$this->placeholder}", $value);

            } else {

                $this->pushCollection('where', "NOT {$data['column']} {$operator} {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function whereIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($value);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function whereNotIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($value);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function whereNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function whereNotNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function orWhere(string $column, string $operator, string|int $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if ($this->useConjunction)
        {
            $this->pushCollection('where', "OR {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            $this->pushCollection('where', "{$data['column']} {$operator} {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereNot(string $column, string $operator, string|int $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into where collection
        if ($this->useConjunction)
        {
            $this->pushCollection('where', "OR NOT {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            $this->pushCollection('where', "NOT {$data['column']} {$operator} {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($value);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function orWhereNotIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($value);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function orWhereNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function orWhereNotNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function whereLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('where', "WHERE {$column} LIKE {$this->placeholder}", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} LIKE {$this->placeholder}", $value);

            } else {

                $this->pushCollection('where', "{$column} LIKE {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function whereNotLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('where', "WHERE {$column} NOT LIKE {$this->placeholder}", $value);

        } else {

            if ($this->useConjunction)
            {
                $this->pushCollection('where', "AND {$column} NOT LIKE {$this->placeholder}", $value);

            } else {

                $this->pushCollection('where', "{$column} NOT LIKE {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('where', "OR {$column} LIKE {$this->placeholder}", $value);

        } else {
            
            $this->pushCollection('where', "{$column} LIKE {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orWhereNotLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('where', "OR {$column} NOT LIKE {$this->placeholder}", $value);

        } else {
            
            $this->pushCollection('where', "{$column} NOT LIKE {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupStart(): PostgreSQLBuilder
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

    public function notGroupStart(): PostgreSQLBuilder
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

    public function orGroupStart(): PostgreSQLBuilder
    {
        // push
        $this->pushCollection('groupWhere', 'OR (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orNotGroupStart(): PostgreSQLBuilder
    {
        // push
        $this->pushCollection('groupWhere', 'OR NOT (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupEnd(): PostgreSQLBuilder
    {
        // push
        $this->pushCollection('groupWhereEnd', ')');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function groupBy(string|array $columns, bool $raw = false): PostgreSQLBuilder
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

    public function having(string $column, string $operator, string|int $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if (empty($this->havingCollection))
        {
            $this->pushCollection('having', "HAVING {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$data['column']} {$operator} {$this->placeholder}", $value);

            } else {

                $this->pushCollection('having', "{$data['column']} {$operator} {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNot(string $column, string $operator, string|int $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if (empty($this->havingCollection))
        {
            $this->pushCollection('having', "HAVING NOT {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND NOT {$data['column']} {$operator} {$this->placeholder}", $value);

            } else {

                $this->pushCollection('having', "NOT {$data['column']} {$operator} {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($this->havingParams);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function havingNotIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($this->havingParams);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function havingNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function havingNotNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function orHaving(string $column, string $operator, string $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if ($this->havingUseConjunction)
        {
            $this->pushCollection('having', "OR {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            $this->pushCollection('having', "{$data['column']} {$operator} {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingNot(string $column, string $operator, string $value, bool $raw = false): PostgreSQLBuilder
    {
        // execute before
        $data = $this->beforeWhere($column, $operator, $value, $raw);

        // push into having collection
        if ($this->havingUseConjunction)
        {
            $this->pushCollection('having', "OR NOT {$data['column']} {$operator} {$this->placeholder}", $value);

        } else {

            $this->pushCollection('having', "NOT {$data['column']} {$operator} {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($this->havingParams);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function orHavingNotIn(string $column, array $value, bool $raw = false): PostgreSQLBuilder
    {
        if (!$raw)
        {
            $column = $this->sanitizeColumn($column);
        }

        // count values
        $totalParams  = count($this->havingParams);
        $placeholders = array_fill(1, $totalParams, $this->placeholder);
        $variables    = implode(', ', $placeholders);

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

    public function orHavingNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function orHavingNotNull(string $column, bool $raw = false): PostgreSQLBuilder
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

    public function havingLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('having', "HAVING {$column} LIKE {$this->placeholder}", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} LIKE {$this->placeholder}", $value);

            } else {

                $this->pushCollection('having', "{$column} LIKE {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingNotLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('having', "HAVING {$column} NOT LIKE {$this->placeholder}", $value);

        } else {

            if ($this->havingUseConjunction)
            {
                $this->pushCollection('having', "AND {$column} NOT LIKE {$this->placeholder}", $value);

            } else {

                $this->pushCollection('having', "{$column} NOT LIKE {$this->placeholder}", $value);
            }
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('having', "OR {$column} LIKE {$this->placeholder}", $value);

        } else {
            
            $this->pushCollection('having', "{$column} LIKE {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function orHavingNotLike(string $column, string $value, string $method = 'both', bool $raw = false): PostgreSQLBuilder
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
            $this->pushCollection('having', "OR {$column} NOT LIKE {$this->placeholder}", $value);

        } else {
            
            $this->pushCollection('having', "{$column} NOT LIKE {$this->placeholder}", $value);
        }

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingGroupStart(): PostgreSQLBuilder
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

    public function havingNotGroupStart(): PostgreSQLBuilder
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

    public function havingOrGroupStart(): PostgreSQLBuilder
    {
        // push
        $this->pushCollection('groupHaving', 'OR (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingOrNotGroupStart(): PostgreSQLBuilder
    {
        // push
        $this->pushCollection('groupHaving', 'OR NOT (');

        // return instance
        return $this;
    }

    //=================================================================================================

    public function havingGroupEnd(): PostgreSQLBuilder
    {
        // push
        $this->pushCollection('groupHavingEnd', ')');

        // return instance
        return $this;
    }

    //=================================================================================================
    
    public function orderBy(string $column, string $order = 'ASC', bool $raw = false): PostgreSQLBuilder
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

    public function offset(int $num): PostgreSQLBuilder 
    {
        $this->offsetCount = $num;

        // return instance
        return $this;
    }

    //=================================================================================================

    public function limit(int $num): PostgreSQLBuilder
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

        // set table
        $table = $this->db->escape($this->from, 'identifier');

        // set command
        if ($command === 'select')
        {
            // select string
            $selectString = ($this->distinct) ? "SELECT DISTINCT" : "SELECT";

            // store into prepared query
            $this->preparedQuery = $selectString;
            
            // column collection
            // and string
            $this->selectCollection = empty($this->selectCollection) ? [ "{$table}.*" ] : $this->selectCollection;
            $columnString = implode(', ', $this->selectCollection);

            // store into prepared query
            $this->preparedQuery .= " {$columnString}";

        } elseif ($command === 'count') {

            // store into prepared query
            $this->preparedQuery = "SELECT COUNT(*) AS total";
        }

        // from string
        $fromString = "FROM {$table}";

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

    protected function extractPostgresPlaceholders(string $sql): array
    {
        // AI GENERATED SNIPPETS //

        $placeholders = [];
        $length = strlen($sql);
        $inString = false;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            // Handle string start/end
            if ($char === "'" && !$escaped) {
                $inString = !$inString;
                continue;
            }

            // Handle escape by doubling single quotes inside strings (e.g., 'O''Reilly')
            if ($char === "'" && $inString && $i + 1 < $length && $sql[$i + 1] === "'") {
                $i++; // Skip the next quote
                continue;
            }

            // Check for placeholders only when not in a string
            if (!$inString && $char === '$') {
                $j = $i + 1;
                $number = '';
                while ($j < $length && ctype_digit($sql[$j])) {
                    $number .= $sql[$j];
                    $j++;
                }

                if ($number !== '') {
                    $placeholders[] = '$' . $number;
                    $i = $j - 1;
                }
            }

            $escaped = ($char === '\\' && !$escaped);
        }

        $placeholders = array_unique($placeholders);
        natsort($placeholders);

        // return
        return array_values($placeholders);
    }

    //=================================================================================================

    public function compilePreparedQuery(string $query = '', array $params = []): string
    {
        // move to new variable
        $query  = empty($query) ? $this->preparedQuery : $query;
        $params = empty($params) ? $this->preparedParams : $params;

        // mutate params
        foreach ($params as $n => $param):

            $params[$n] = $this->db->escape($param, 'literal');

        endforeach;
        
        // placeholders
        $placeholders = $this->extractPostgresPlaceholders($query);
        $tempQuery    = $query;

        foreach ($placeholders as $i => $placeholder):

            // find position
            $len = strlen($placeholder);
            $pos = strpos($tempQuery, $placeholder);

            // string replace
            $tempQuery = substr_replace($tempQuery, strval($params[$i]), $pos, $len);

        endforeach;

        // check temp query
        $compiledQuery = $tempQuery;

        // return
        return $compiledQuery;
    }

    //=================================================================================================

    public function countAll(bool $resetQuery = true): int
    {
        $table  = $this->db->escape($this->from, 'identifier');
        $query  = "SELECT COUNT(*) AS total FROM {$table}";
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

        // seed placeholder
        $this->seedPlaceholder();

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

    public function get(bool $resetQuery = true): PostgreSQL
    {
        // build query
        $this->buildGetQuery('select');

        // build params
        $this->buildGetParams();

        // seed placeholder
        $this->seedPlaceholder();

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

        // seed placeholder
        $this->seedPlaceholder();

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

    public function resetQuery(): PostgreSQLBuilder
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

    public function set(string|array $data, string|int|null|bool $value = false): PostgreSQLBuilder
    {
        // push data
        $this->pushSetData($data, $value);

        // return instance
        return $this;
    }

    //=================================================================================================

    public function setReplace(string|array $data, string|int|null $oldValue = '', string|int|null $newValue = '', bool $raw = false): PostgreSQLBuilder
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
            'oldValue' => ($raw) ? $oldValue : $this->placeholder,
            'newValue' => ($raw) ? $newValue : $this->placeholder,
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

    public function increment(string $column, int $incNum = 1, bool $raw = false): PostgreSQLBuilder
    {
        $column = ($raw) ? $column : $this->sanitizeColumn($column);

        // set
        $this->pushSetData($column, "{$column}+{$incNum}", false);

        // return instance
        return $this;
    }

    //=================================================================================================

    public function decrement(string $column, int $incNum = 1, bool $raw = false): PostgreSQLBuilder
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

        // set table
        $table = $this->db->escape($this->from, 'identifier');

        // build string
        switch ($type) {
            case 'insert':
                $columns = implode(', ', $this->setDataCollection['key']);
                $values  = implode(', ', $this->setDataCollection['value']);
                $this->preparedQuery = "INSERT INTO {$table} ({$columns}) VALUES ({$values})";
                break;

            case 'insertBulk':
                $values  = [];
                $columns = implode(', ', $this->setDataBatchCollection['key']);

                foreach ($this->setDataBatchCollection['value'] as $value):

                    array_push($values, '('. implode(', ', $value) .')');

                endforeach;

                $values = implode(', ', $values);
                $this->preparedQuery = "INSERT INTO {$table} ({$columns}) VALUES {$values}";
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
                $this->preparedQuery = "UPDATE {$table} SET {$columns}";

                // where string        
                if (!empty($this->whereCollection))
                {
                    $whereString = implode(" ", $this->whereCollection);

                    // store into prepared query
                    $this->preparedQuery .= " {$whereString}";
                }
                break;

            case 'updateBulk':
                $tempTableName = $this->db->escape('tmp_' . time() . '_' . random_int(10000, 99999), 'identifier');
                $keys          = $this->setDataBatchCollection['key'];
                $setQuery      = [];

                // find data_type based on column name
                $placeholders = $this->generatePlaceholder(2, (count($keys) + 1));
                $query        = "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = \$1 AND column_name IN (". implode(', ', $placeholders) .")";
                $params       = [ $this->from ];
                $rawColumns   = [];

                // add to params
                foreach ($keys as $column):

                    array_push($params, str_ireplace(['`', '"', "'"], '', $column));
                    array_push($rawColumns, str_ireplace(['`', '"', "'"], '', $column));

                endforeach;

                // generate prepared query
                $columnData = $this->db->preparedQuery($query, $params)->getResultArray();
                $dataType   = [];

                foreach ($columnData as $item):

                    $dataType[$item['column_name']] = strtoupper($item['data_type']);

                endforeach;

                // create keys/columns
                foreach ($keys as $key):

                    if ($key !== $this->setColumnBatch)
                    {
                        array_push($setQuery, "{$key} = {$tempTableName}.{$key}");
                    }

                endforeach;

                // create values
                $values = [];
                $params = $this->setDataBatchParams;

                foreach ($this->setDataBatchCollection['value'] as $value):

                    $str = "(";

                    foreach ($value as $i => $item)
                    {
                        $val = array_shift($params);

                        if ($i === 0)
                        {
                            $str .= $this->db->escape($val, 'literal') . "::" . $dataType[$rawColumns[$i]];

                        } else {

                            $str .= ', ' . $this->db->escape($val, 'literal') . "::" . $dataType[$rawColumns[$i]];
                        }
                    }

                    $str .= ")";

                    // store to values
                    array_push($values, $str);

                endforeach;

                // implode
                $valueQuery = implode(", ", $values);
                $asQuery    = implode(", ", $this->setDataBatchCollection['key']);

                // build prepared query
                $this->preparedQuery = "UPDATE {$table} SET " . implode(", ", $setQuery) . " FROM (VALUES {$valueQuery}) AS {$tempTableName}({$asQuery}) WHERE {$table}.{$this->setColumnBatch} = {$tempTableName}.{$this->setColumnBatch}";
                break;

            case 'upsert':
                $keys         = implode(", ", $this->setDataCollection['key']);
                $placeholders = implode(", ", $this->setDataCollection['value']);
                $sets         = [];

                foreach ($this->setDataCollection['key'] as $column):

                    if ($this->sanitizeColumn($column) !== $this->setColumnBatch && !in_array($column, $this->setExcludedColumns))
                    {
                        array_push($sets, "{$column} = EXCLUDED.{$column}");
                    }
                    
                endforeach;

                $this->preparedQuery = "INSERT INTO {$table} ($keys) VALUES ({$placeholders}) ON CONFLICT({$this->setColumnBatch}) DO UPDATE SET ". implode(", ", $sets);
                break;

            case 'upsertBulk':
                $keys = implode(", ", $this->setDataBatchCollection['key']);
                $sets = [];

                foreach ($this->setDataBatchCollection['key'] as $column):

                    if ($this->sanitizeColumn($column) !== $this->setColumnBatch && !in_array($column, $this->setExcludedColumns))
                    {
                        array_push($sets, "{$column} = EXCLUDED.{$column}");
                    }
                    
                endforeach;
                
                $placeholders = [];

                foreach ($this->setDataBatchCollection['value'] as $values):

                    array_push($placeholders, "(" . implode(", ", $values) . ")");

                endforeach;

                $placeholders = implode(", ", $placeholders);

                $this->preparedQuery = "INSERT INTO {$table} ($keys) VALUES {$placeholders} ON CONFLICT({$this->setColumnBatch}) DO UPDATE SET ". implode(", ", $sets);
                break;

            case 'delete':
                $this->preparedQuery = "DELETE FROM {$table}";

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
                $this->preparedQuery = "UPDATE {$table} SET {$setReplace}";
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

        // set seed on placeholder
        $this->seedPlaceholder();

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // reset query
        $this->resetQuery();

        // result array
        $result = $db->getResult();

        // return
        return [
            'status'   => (empty($result['error'])) ? true : false,
            'insertID' => $result['insert_id'],
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

            // set seed on placeholder
            $this->seedPlaceholder();
    
            // insert bulk start
            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
    
            // conn
            $result = $db->getResult();
        }

        // reset query
        $this->resetQuery();

        // return
        return (empty($result['error'])) ? true : false;
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

        // seed placeholder
        $this->seedPlaceholder();

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // reset query
        $this->resetQuery();

        // result array
        $result = $db->getResult();

        // return
        return [
            'status'        => (empty($result['error'])) ? true : false,
            'affected_rows' => $result['affected_rows'],
        ];
    }

    //=================================================================================================

    public function updateBulk(array $data, string $targetColumn, array $excludedColumns = []): bool
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
                $this->updateBulk($dataPart, $targetColumn);

                $num++;

            endforeach;

            // conn
            $result = $this->db->getResult();

        } else {

            // set table column batch
            $this->setColumnBatch     = $this->sanitizeColumn($targetColumn);

            // push data
            $this->pushSetDataBatch($data);

            // build
            $this->buildSetQuery('updateBulk');

            // build
            $this->buildSetParams('updateBulk');

            // set seed on placeholder
            $this->seedPlaceholder();

            // insert bulk start
            $db = $this->db->rawQuery($this->preparedQuery);

            // conn
            $result = $db->getResult();
        }

        // reset query
        $this->resetQuery();

        // return
        return (empty($result['error'])) ? true : false;
    }

    //=================================================================================================

    public function upsert(array $data, string $targetColumn, array $excludedColumns = []): bool
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
        return (empty($result['error'])) ? true : false;
    }

    //=================================================================================================

    public function upsertBulk(array $data, string $targetColumn, array $excludedColumns = []): bool
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
        return (empty($result['error'])) ? true : false;
    }

    //=================================================================================================

    public function delete(): array
    {
        // build query
        $this->buildSetQuery('delete');

        // build set params
        $this->buildSetParams('delete');

        // seed placeholder
        $this->seedPlaceholder();

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // reset query
        $this->resetQuery();

        // result array
        $result = $db->getResult();

        // return
        return [
            'status'        => (empty($result['error'])) ? true : false,
            'affected_rows' => $result['affected_rows'],
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

        // seed placeholder
        $this->seedPlaceholder();

        // check if prepared params is not empty
        if (empty($this->preparedParams))
        {
            $db = $this->db->rawQuery($this->preparedQuery);

        } else {

            $db = $this->db->preparedQuery($this->preparedQuery, $this->preparedParams);
        }

        // result array
        $result = $db->getResult();

        // return
        return [
            'status'        => (empty($result['error'])) ? true : false,
            'affected_rows' => $result['affected_rows'],
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
        $table = $this->db->escape($this->from, 'identifier');
        $db    = $this->db->rawQuery("TRUNCATE TABLE {$table}");

        // conn
        $result = $db->getResult();

        // reset query
        $this->resetQuery();

        // return
        return (empty($result['error'])) ? true : false;
    }

    //=================================================================================================

    public function emptyTable(): bool
    {
        // return db
        $table = $this->db->escape($this->from, 'identifier');
        $db    = $this->db->rawQuery("DELETE FROM {$table}");

        // conn
        $result = $db->getResult();

        // reset query
        $this->resetQuery();

        // return
        return (empty($result['error'])) ? true : false;
    }

    //=================================================================================================

    protected function addPrefix(string $input, PostgreSQL $db): string
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
                $columnArray[1] = $db->escape($columnArray[1], 'identifier');

                if ($this->prefix !== substr($columnArray[0], 0, strlen($this->prefix)))
                {
                    // add prefix
                    $columnArray[0] = $db->escape($this->prefix . $columnArray[0], 'identifier');

                    // implode
                    $column = implode('.', $columnArray);

                } else {

                    $columnArray[0] = $db->escape($columnArray[0], 'identifier');
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
                $preparedValue = ($prepared) ? $this->placeholder : $item;

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
            $preparedValue = ($prepared) ? $this->placeholder : $value;

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
                array_push($values, $this->placeholder);

                // push into params
                array_push($this->setDataBatchParams, $value);

            endforeach;

            // set update
            array_push($this->setDataBatchCollection['value'], $values);

        endforeach;
    }

    //==================================================================================================
}