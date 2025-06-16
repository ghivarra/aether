<?php

declare(strict_types = 1);

namespace Aether\Validation\Rules;

use Aether\Validation\Rules\BaseRules;

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

class ComparativeFieldRules extends BaseRules
{
    public function differ(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        return isset($data[$field]) && $this->toString($str) !== $this->toString($data[$field]);
    }

    //============================================================================================

    public function match(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        return isset($data[$field]) && $this->toString($str) === $this->toString($data[$field]);
    }

    //============================================================================================

    public function exact_length_with(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        if (!isset($data[$field]))
        {
            return false;
        }

        $str = $this->toString($str);
        $str = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        $value = $this->toString($data[$field]);
        $value = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

        return $str === $value;
    }

    //============================================================================================

    public function not_exact_length_with(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        if (!isset($data[$field]))
        {
            return false;
        }

        $str = $this->toString($str);
        $str = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        $value = $this->toString($data[$field]);
        $value = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

        return $str !== $value;
    }

    //============================================================================================

    public function greater_than_field(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        if (is_null($str) || !isset($data[$field]))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($data[$field]) && $this->formatNumber($str) > $this->formatNumber($data[$field]);
    }

    //============================================================================================

    public function greater_than_or_equal_to_field(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        if (is_null($str) || !isset($data[$field]))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($data[$field]) && $this->formatNumber($str) >= $this->formatNumber($data[$field]);
    }

    //============================================================================================

    public function less_than_field(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        if (is_null($str) || !isset($data[$field]))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($data[$field]) && $this->formatNumber($str) < $this->formatNumber($data[$field]);
    }

    //============================================================================================

    public function less_than_or_equal_to_field(string|float|int|null $str = null, string|int $field = 0, array $data = []): bool
    {
        if (is_null($str) || !isset($data[$field]))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($data[$field]) && $this->formatNumber($str) > $this->formatNumber($data[$field]);
    }

    //============================================================================================
}