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
    // tables
    public string $prefix = '';
    public string $from = '';

    // select
    public array $selectCollection = [];
    public bool $distinct = false;
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
    public string $resultQuery = '';
    public string $preparedQuery = '';
    public array $preparedParams = [];

    // subquery
    public bool $onSubquery = false;
    public array $subqueries = [];

    // create-update
    public array $setDataParams = [];
    public array $setDataCollection = [
        'key'   => [],
        'value' => [],
    ];

    // create-update batch
    public string $setColumnBatch = '';
    public array $setDataBatchParams = [];
    public array $setDataBatchCollection = [
        'key'   => [],
        'value' => [],
    ];

    // replace
    public array $setReplaceParams = [];
    public array $setReplaceCollection = [];

    //==================================================================================================
}