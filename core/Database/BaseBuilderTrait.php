<?php

declare(strict_types = 1);

namespace Aether\Database;

/** 
 * Aether Query Builder Base Trait
 * 
 * @class Aether\Database\BaseBuilder
**/

trait BaseBuilderTrait
{
    public array $selectCollection = [];
    public bool $distinct = false;
    public string $from = '';
    public array $joinCollection = [];
    public array $whereCollection = [];
    public bool $useConjunction = true;
    public array $groupByCollection = [];
    public array $havingCollection = [];
    public array $orderByCollection = [];
    public int|null $limit = null;
    public int|null $offset = null;
    public array $setCollection = [];
    public string $resultQuery = '';
    public string $preparedQuery = '';
    public array $preparedParams = [];
    public string $prefix = '';

    //==================================================================================================

    public function addPrefix(string $input): string
    {
        // Generated this script 95% using OpenAI, It helped me so much as I totally clueless
        // about regex pattern, I know it is so powerful and what it can do but still...

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

        $pattern      = '/(?:(\b[a-zA-Z_][a-zA-Z0-9_]*)\.)?([a-zA-Z_][a-zA-Z0-9_]*)/';
        $result       = '';
        $offset       = 0;
        $prefix       = $this->prefix;
        $contextTable = $this->from;

        // Match quoted strings to skip them
        preg_match_all('/(["\'])(?:\\\\.|(?!\1).)*\1/', $input, $quotedMatches, PREG_OFFSET_CAPTURE);

        foreach ($quotedMatches[0] as $match):

            $quoteStart = $match[1];
            $quoteEnd = $quoteStart + strlen($match[0]);

            // Process content before the quoted string
            $before = substr($input, $offset, $quoteStart - $offset);
            $before = preg_replace_callback($pattern, function ($matches) use ($contextTable, $prefix) {
                $table = $matches[1] ?? null;
                $column = $matches[2];

                if ($table)
                {
                    return "`{$prefix}{$table}`.$column";

                } else {

                    return "`{$contextTable}`.$column";
                }

            }, $before);

            $result .= $before;

            // Append the quoted string untouched
            $result .= $match[0];
            $offset = $quoteEnd;

        endforeach;

        // Process remaining string after last quote
        if ($offset < strlen($input))
        {
            $rest = substr($input, $offset);
            $rest = preg_replace_callback($pattern, function ($matches) use ($contextTable, $prefix) {
                $table = $matches[1] ?? null;
                $column = $matches[2];

                if ($table)
                {
                    return "`{$prefix}{$table}`.$column";

                } else {
                    
                    return "`{$contextTable}`.$column";
                }

            }, $rest);

            $result .= $rest;
        }

        // return
        return $result;
    }

    //==================================================================================================

    public function rawQuery(string $query): mixed
    {
        return '';
    }

    //==================================================================================================
}