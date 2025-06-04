<?php

declare(strict_types = 1);

namespace Aether\Database;

use Aether\Database\DriverInterface;
use Aether\Database\Builder;

/** 
 * Base Model Trait
 * 
 * A trait to be used on base model for variables that was used on operation only
 * 
 * @class Aether\Database\BaseModelTrait
**/

trait BaseModelTrait
{
    protected DriverInterface $db;
    protected Builder $builder;
    protected bool $withDeletedOption = false;
    protected bool $deletedOnlyOption = false;
    protected bool $useConditional = false;
}