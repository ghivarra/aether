<?php

declare(strict_types = 1);

namespace Aether\Database;

use Aether\Database\DriverInterface;

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
    public array $joinParams = [];
    public array $whereCollection = [];
    public array $whereParams = [];
    public bool $useConjunction = true;
    public array $groupByCollection = [];
    public array $havingCollection = [];
    public array $havingParams = [];
    public bool $havingUseConjunction = true;
    public array $orderByCollection = [];
    public int|null $limitCount = null;
    public int|null $offsetCount = null;
    public array $setCollection = [];
    public array $setParams = [];
    public string $resultQuery = '';
    public string $preparedQuery = '';
    public array $preparedParams = [];
    public string $prefix = '';
    public bool $onSubquery = false;
    public array $subqueries = [];

    //==================================================================================================

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
}