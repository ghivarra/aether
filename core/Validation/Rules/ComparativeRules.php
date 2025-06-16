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

class ComparativeRules extends BaseRules
{
    public function required(string|float|int|null $str = null): bool
    {
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        if ($len < 1 || is_null($str))
        {
            return false;
        }

        return true;
    }

    //============================================================================================

    public function empty(string|float|int|null $str = null): bool
    {
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        if ($len < 1 || is_null($str))
        {
            return true;
        }

        return false;
    }

    //============================================================================================
    
    public function exact_length(string|float|int|null $str = null, string|int $value): bool
    {
        $str = $this->toString($str);
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        return $len === intval($value);
    }

    //============================================================================================

    public function not_exact_length(string|float|int|null $str = null, string|int $value): bool
    {
        $str = $this->toString($str);
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        return $len !== intval($value);
    }

    //============================================================================================

    public function exact_length_in(string|float|int|null $str = null, string $value): bool
    {
        $str = $this->toString($str);
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        // explode value
        $value = explode(',', $value);

        foreach ($value as $num):

            if ($len === intval($num))
            {
                return true;
            }

        endforeach;

        // return
        return false;
    }

    //============================================================================================

    public function not_exact_length_in(string|float|int|null $str = null, string $value): bool
    {
        $str = $this->toString($str);
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        // explode value
        $value = explode(',', $value);

        foreach ($value as $num):

            if ($len === intval($num))
            {
                return false;
            }

        endforeach;

        // return
        return true;
    }

    //============================================================================================

    public function max_length(string|float|int|null $str = null, string|int $value): bool
    {
        $str = $this->toString($str);
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        return $len <= intval($value);
    }

    //============================================================================================

    public function min_length(string|float|int|null $str = null, string|int $value): bool
    {
        $str = $this->toString($str);
        $len = function_exists('mb_strlen') ? mb_strlen($str) : strlen($str);

        return $len >= intval($value);
    }

    //============================================================================================

    public function equal(string|float|int|null $str = null, string|int $value): bool
    {
        return $this->toString($str) === $this->toString($value);
    }

    //============================================================================================

    public function not_equal(string|float|int|null $str = null, string|int $value): bool
    {
        return $this->toString($str) !== $this->toString($value);
    }
    
    //============================================================================================

    public function in_list(string|float|int|null $str = null, string $value): bool
    {
        if (is_null($str))
        {
            return false;
        }

        $str   = $this->toString($str);
        $value = explode(',', $value);

        foreach ($value as $data):

            if ($str === $this->toString($data))
            {
                return true;
            }

        endforeach;

        return false;
    }

    //============================================================================================

    public function not_in_list(string|float|int|null $str = null, string $value): bool
    {
        if (is_null($str))
        {
            return false;
        }

        $str   = $this->toString($str);
        $value = explode(',', $value);

        foreach ($value as $data):

            if ($str === $this->toString($data))
            {
                return false;
            }

        endforeach;

        return true;
    }

    //============================================================================================

    public function greater_than(string|float|int|null $str = null, string|int $value): bool
    {
        if (is_null($str))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($value) && $this->formatNumber($str) > $this->formatNumber($value);
    }

    //============================================================================================

    public function greater_than_or_equal_to(string|float|int|null $str = null, string|int $value): bool
    {
        if (is_null($str))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($value) && $this->formatNumber($str) >= $this->formatNumber($value);
    }

    //============================================================================================

    public function less_than(string|float|int|null $str = null, string|int $value): bool
    {
        if (is_null($str))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($value) && $this->formatNumber($str) < $this->formatNumber($value);
    }

    //============================================================================================

    public function less_than_or_equal_to(string|float|int|null $str = null, string|int $value): bool
    {
        if (is_null($str))
        {
            return false;
        }

        return is_numeric($str) && is_numeric($value) && $this->formatNumber($str) <= $this->formatNumber($value);
    }

    //============================================================================================
}