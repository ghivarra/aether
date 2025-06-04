<?php

declare(strict_types = 1);

namespace Aether;

use Aether\Database\BaseModelTrait;
use Aether\Database;

/** 
 * Model
 * 
 * Simple ORM Model, without event trigger on change, etc.
 * just extension of Query Builder with soft deletes, time creation, update, etc.
 * 
 * @class Aether\Model
**/

class Model
{
    use BaseModelTrait;

    // table description
    protected string $table = '';
    protected string $primaryKey = '';
    protected string $primaryKeyType = 'integer';

    // model option
    protected bool $useAutoIncrement = true;
    protected bool $useSoftDelete = false;

    // insert/update/upsert option
    protected array $allowedFields = [];
    protected bool $allowEmptyInsert = false;
    
    // timestamps
    protected bool $useTimestamps = false;
    protected string $dateFormat = 'datetime'; // datetime, date, or just time
    protected string $createdField = 'created_at';
    protected string $updatedField = 'updated_at';
    protected string $deletedField = 'deleted_at';

    // insert/update/upsert general validation
    protected bool $useValidation = true;
    protected array $validationRules = [];
    protected array $validationMessages = [];
    
    // callbacks
    protected bool $useCallbacks = false;

    protected array $beforeFind = [];
    protected array $beforeInsert = [];
    protected array $beforeInsertBulk = [];
    protected array $beforeUpdate = [];
    protected array $beforeUpdateBulk = [];
    protected array $beforeUpsert = [];
    protected array $beforeUpsertBulk = [];
    protected array $beforeDelete = [];

    protected array $afterFind = [];
    protected array $afterInsert = [];
    protected array $afterInsertBulk = [];
    protected array $afterUpdate = [];
    protected array $afterUpdateBulk = [];
    protected array $afterUpsert = [];
    protected array $afterUpsertBulk = [];
    protected array $afterDelete = [];

    //======================================================================================================

    public function __construct(string|null $defaultConnection = null)
    {
        // connect to database
        if (is_null($defaultConnection))
        {
            $this->db = Database::connect();

        } else {

            $this->db = Database::connect($defaultConnection);
        }

        // set builder
        $this->builder = $this->db->table($this->table);
    }

    //======================================================================================================

    public function __call(string $method, array $arguments): Model
    {
        // check if where then use group start
        if (str_contains($method, 'where') || str_contains($method, 'Where'))
        {
            $this->useConditional = true;
            $this->builder->groupStart();
        }

        // redirect to builder if not exist
        $this->builder->$method(...$arguments);

        // return this
        return $this;
    }

    //======================================================================================================

    protected function triggerCallback(string $action, array $data): array
    {
        // call functions
        if (isset($this->$action))
        {  
            // iterate functions
            foreach ($this->$action as $function):

                $data = $this->$function($data);

            endforeach;
        }

        // return
        return $data;
    }

    //======================================================================================================

    public function checkSoftDeleteOption(): void
    {
        // check if conditional used
        if ($this->useConditional)
        {
            $this->builder->groupEnd();
        }

        // check soft delete
        if ($this->useSoftDelete && !$this->withDeletedOption)
        {              
            if ($this->deletedOnlyOption) {

                $this->builder->whereNotNull($this->deletedField);

            } else {

                $this->builder->whereNull($this->deletedField);
            }
        }
    }

    //======================================================================================================

    public function checkBeforeFind(bool $resetQuery = false): void
    {
        if ($resetQuery)
        {
            $this->builder->resetQuery();
            $this->builder = $this->db->table($this->table);
        }

        // set multiple result on
        $this->checkSoftDeleteOption();
    }

    //======================================================================================================

    public function count(bool $resetQuery = true): int
    {
        return $this->countAllResults($resetQuery);
    }

    //======================================================================================================

    public function countAll(bool $resetQuery = true): int
    {
        // call
        $this->checkBeforeFind(true);

        // call if using callback
        if ($this->useCallbacks)
        {
            $this->triggerCallback('beforeFind', ['singleton' => true]);
        }

        // move into data
        $data = [
            'singleton' => true,
            'method'    => __FUNCTION__,
            'data'      => $this->builder->countAll($resetQuery),
        ];
        
        // check if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterFind', $data);
        }

        // return result
        return $data['data'];
    }

    //======================================================================================================

    public function countAllResults(bool $resetQuery = true): int
    {
        // call
        $this->checkBeforeFind(true);

        // call if using callback
        if ($this->useCallbacks)
        {
            $this->triggerCallback('beforeFind', ['singleton' => true]);
        }

        // move into data
        $data = [
            'singleton' => true,
            'method'    => __FUNCTION__,
            'data'      => $this->builder->countAllResults($resetQuery),
        ];
        
        // check if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterFind', $data);
        }

        // return result
        return $data['data'];
    }

    //======================================================================================================

    public function find(bool $resetQuery = true): array
    {
        // call
        $this->checkBeforeFind();

        // call if using callback
        if ($this->useCallbacks)
        {
            $this->triggerCallback('beforeFind', ['singleton' => false]);
        }

        // move into data
        $data = [
            'singleton' => false,
            'method'    => __FUNCTION__,
            'data'      => $this->builder->get($resetQuery)->getResultArray(),
        ];
        
        // check if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterFind', $data);
        }

        // return result
        return $data['data'];
    }

    //======================================================================================================

    public function findAll(bool $resetQuery = true, int|null $limit = null, int|null $offset = null): array
    {
        // call
        $this->checkBeforeFind(true);

        // call if using callback
        if ($this->useCallbacks)
        {
            $this->triggerCallback('beforeFind', ['singleton' => false]);
        }

        // add limit or offset
        if (!is_null($limit))
        {
            $this->builder->limit($limit);
        }

        if (!is_null($offset))
        {
            $this->builder->offset($offset);
        }

        // move into data
        $data = [
            'singleton' => false,
            'method'    => __FUNCTION__,
            'data'      => $this->builder->get($resetQuery)->getResultArray(),
        ];
        
        // check if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterFind', $data);
        }

        // return result
        return $data['data'];
    }

    //======================================================================================================

    public function first(bool $resetQuery = true)
    {
        // call
        $this->checkBeforeFind();

        // call if using callback
        if ($this->useCallbacks)
        {
            $this->triggerCallback('beforeFind', ['singleton' => true]);
        }

        // move into data
        $data = [
            'singleton' => true,
            'method'    => __FUNCTION__,
            'data'      => $this->builder->get($resetQuery)->getRowArray(),
        ];
        
        // check if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterFind', $data);
        }

        // return result
        return $data['data'];
    }

    //======================================================================================================

    public function save()
    {
        
    }

    //======================================================================================================

    public function delete()
    {
        
    }

    //======================================================================================================

    public function purge()
    {
        
    }

    //======================================================================================================

    public function deletedOnly()
    {
        $this->deletedOnlyOption = true;
    }

    //======================================================================================================

    public function withDeleted()
    {
        $this->withDeletedOption = true;
    }

    //======================================================================================================
}