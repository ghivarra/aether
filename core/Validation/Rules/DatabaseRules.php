<?php

declare(strict_types = 1);

namespace Aether\Validation\Rules;

use Aether\Validation\Rules\BaseRules;
use Aether\Database;
use Aether\Database\DriverInterface;

/**
 * This file is heavily inspired or straight up copy paste from
 * CodeIgniter 4 Validation Library
 *
 * The copyright for this library belong to:
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was in below url:
 * 
 * https://github.com/codeigniter4/CodeIgniter4/blob/develop/LICENSE
 * 
 */

class DatabaseRules extends BaseRules
{
    protected DriverInterface $db;

    //===============================================================================================

    public function __construct()
    {
        $this->db = Database::connect();
    }

    //===============================================================================================

    public function is_unique(string|float|int|null $str = null, string $value = ''): bool
    {
        $values  = explode('.', $value);
        $table   = $values[0];
        $column  = $values[1];
        $builder = $this->db->table($table);
        $builder = is_null($str) ? $builder->whereNull($column) : $builder->where($column, '=', $str);
        $count   = $builder->countAllResults();

        return $count < 1;
    }

    //===============================================================================================

    public function is_not_unique(string|float|int|null $str = null, string $value = ''): bool
    {
        $values  = explode('.', $value);
        $table   = $values[0];
        $column  = $values[1];
        $builder = $this->db->table($table);
        $builder = is_null($str) ? $builder->whereNull($column) : $builder->where($column, '=', $str);
        $count   = $builder->countAllResults();

        return $count > 0;
    }

    //===============================================================================================
}