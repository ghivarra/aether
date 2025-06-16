<?php

declare(strict_types = 1);

namespace Aether\Validation\Rules;

class BaseRules
{
    protected function toString(mixed $str): string
    {
        return is_string($str) ? $str : strval($str);
    }

    //============================================================================================

    protected function formatNumber(string|int|float $str): int|float
    {
        if (is_string($str))
        {
            $str = str_contains($str, '.') ? floatval($str) : intval($str);
        }

        return $str;
    }

    //============================================================================================
}