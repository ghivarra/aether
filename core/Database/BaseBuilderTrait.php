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
    public array $groupByCollection = [];
    public array $havingCollection = [];
    public array $orderByCollection = [];
    public int|null $limit = null;
    public int|null $offset = null;
    public array $setCollection = [];
    public string $resultQuery = '';
    public array $preparedQuery = [];
    public mixed $db = null;
    public string $prefix = '';

    //==================================================================================================

    public function compile(): void
    {

    }

    //==================================================================================================

    public function rawQuery(string $query): mixed
    {
        return '';
    }

    //==================================================================================================
}