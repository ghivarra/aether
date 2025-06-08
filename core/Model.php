<?php

declare(strict_types = 1);

namespace Aether;

use Aether\Database\BaseModelTrait;
use Aether\Database;
use Aether\Exception\SystemException;

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

    // insert/update/upsert option
    protected array $allowedFields = [];
    protected bool $allowEmptyInsert = false;
    
    // timestamps
    protected bool $useTimestamps = false;
    protected string $dateFormat = 'datetime'; // datetime, date, or just time
    protected string $createdField = 'created_at';
    protected string $updatedField = 'updated_at';

    protected bool $useSoftDelete = false;
    protected string $deletedField = 'deleted_at';
    
    // callbacks
    protected bool $useCallbacks = false;

    protected array $beforeFind = [];
    protected array $beforeInsert = [];
    protected array $beforeUpdate = [];
    protected array $beforeSave = [];
    protected array $beforeDelete = [];

    protected array $afterFind = [];
    protected array $afterInsert = [];
    protected array $afterUpdate = [];
    protected array $afterSave = [];
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

        // add primary keys to allowed fields
        if (!in_array($this->primaryKey, $this->allowedFields))
        {
            array_push($this->allowedFields, $this->primaryKey);
        }
    }

    //======================================================================================================

    public function __call(string $method, array $arguments): Model
    {
        // check if where then use group start
        if (str_contains($method, 'where') && !$this->useConditional || str_contains($method, 'Where') && !$this->useConditional)
        {
            $this->useConditional = true;
            $this->builder->groupStart();
        }

        // give warning and throw error on using get, getRowArray, and getResultArray
        $restrictedMethod = ['get', 'getRowArray', 'getResultArray'];

        if (in_array($method, $restrictedMethod))
        {
            $message = (AETHER_ENV === 'development') ? 'You should not use one of these methods/functions in Model: ' . implode(', ', $restrictedMethod) : 'Failed to utilize ORM Model';

            throw new SystemException($message, 500);
        }

        // give warning
        $restrictedModify = ['insertBulk', 'updateBulk', 'upsert', 'upsertBulk'];

        if (in_array($method, $restrictedModify))
        {
            $message = (AETHER_ENV === 'development') ? 'You should not use one of these methods/functions in Model: ' . implode(', ', $restrictedModify) . '. Use one of these instead: insert (also detect if using bulk/batch insert out of the box), update (detect if using bulk/batch update out of the box), and save (instead of upsert)' : 'Failed to utilize ORM Model';

            throw new SystemException($message, 500);
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

    protected function generateTimestamp(): string
    {
        switch ($this->dateFormat) {
            case 'date':
                return date('Y-m-d');
                break;

            case 'time':
                return date('H:i:s');
                break;

            case 'datetime':
                return date('Y-m-d H:i:s');
                break;
            
            default:
                return ''; // return, not valid
                break;
        }
    }

    //======================================================================================================

    protected function checkSoftDeleteOption(): void
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

    protected function checkBeforeFind(bool $resetQuery = false): void
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

    protected function checkBeforeInput(array $data, string $type): array
    {
        // check input cannot be empty
        if (empty($data))
        {
            throw new SystemException('Inputted data cannot be empty.', 500);
        }

        // resort array
        $newData = array_merge($data);

        // sort array
        // check singleton
        $singleton = isset($data[0], $data[1]) ? false : true;

        // sanitize field
        if ($singleton)
        {
            foreach ($newData as $key => $value):

                if (!in_array($key, $this->allowedFields))
                {
                    unset($newData[$key]);

                } else {

                    $newData[$key] = $value;
                }

            endforeach;

            if (empty($newData))
            {
                throw new SystemException('Inputted data cannot be empty.', 500);
            }

            // if using timestamp, then add
            if ($this->useTimestamps)
            {
                $timestamp = $this->generateTimestamp();

                if ($type === 'update')
                {
                    $newData[$this->updatedField] = $timestamp;

                } else {

                    $newData[$this->createdField] = $timestamp;
                    $newData[$this->updatedField] = $timestamp;

                    if ($this->useSoftDelete)
                    {
                        $newData[$this->deletedField] = null;
                    }
                }
            }

        } else {

            foreach ($newData as $i => $item):

                // sanitize fields
                foreach ($item as $key => $value)
                {
                    if (!in_array($key, $this->allowedFields))
                    {
                        unset($newData[$i][$key]);
    
                    } else {
    
                        $newData[$i][$key] = $value;
                    }

                    if (empty($newData[$i]))
                    {
                        throw new SystemException('Inputted data cannot be empty.', 500);
                    }
                }

                // if using timestamp, then add
                if ($this->useTimestamps)
                {
                    $timestamp = $this->generateTimestamp();

                    if ($type === 'update')
                    {
                        $newData[$i][$this->updatedField] = $timestamp;

                    } else {

                        $newData[$i][$this->createdField] = $timestamp;
                        $newData[$i][$this->updatedField] = $timestamp;
                        
                        if ($this->useSoftDelete)
                        {
                            $newData[$i][$this->deletedField] = null;
                        }
                    }
                }

            endforeach;
        }

        // return
        return [
            'singleton'     => $singleton,
            'sanitizedData' => $newData,
        ];
    }

    //======================================================================================================

    public function count(bool $resetQuery = true): int
    {
        return $this->countAllResults($resetQuery);
    }

    //======================================================================================================

    public function countAll(bool $resetQuery = true): int
    {
        // reset conditional
        $this->useConditional = false;

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
        $this->checkBeforeFind(false);

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
        $this->checkBeforeFind(false);

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
        // reset conditional
        $this->useConditional = false;

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

    public function first(bool $resetQuery = true): array
    {
        // call
        $this->checkBeforeFind(false);

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

    public function insert(array $data): array
    {
        // sanitize fields and checking singleton or not
        $data = $this->checkBeforeInput($data, 'insert');

        // do before insert
        // call if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('beforeInsert', $data);
        }

        // if
        if ($data['singleton'])
        {
            // execute query
            $result = $this->builder->insert($data['sanitizedData']);

        } else {

            // execute query
            $result = $this->builder->insertBulk($data['sanitizedData']);
        }

        // set data
        $data = [
            'singleton'     => $data['singleton'],
            'result_status' => is_bool($result) ? $result : $result['status'],
        ];

        // do after insert
        // call if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterInsert', $data);
        }

        // return
        return $data;
    }

    //======================================================================================================

    public function update(array $data, string|null $columnKey = null): array
    {
        // sanitize fields and checking singleton or not
        $data = $this->checkBeforeInput($data, 'update');

        // do before update
        // call if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('beforeUpdate', $data);
        }

        // check soft delete
        $this->checkSoftDeleteOption();

        // if
        if ($data['singleton'])
        {
            // insert single
            $result = $this->builder->update($data['sanitizedData']);

        } else {

            // set column key
            $columnKey = is_null($columnKey) ? $this->primaryKey : $columnKey;

            // insert bulk
            $result = $this->builder->updateBulk($data['sanitizedData'], $columnKey);
        }

        // set data
        $data = [
            'singleton'     => $data['singleton'],
            'result_status' => is_bool($result) ? $result : $result['status'],
        ];

        // do after update
        // call if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterUpdate', $data);
        }

        // return
        return $data;
    }

    //======================================================================================================

    public function save(array $data, string|null $columnKey = null): array
    {
        // sanitize fields and checking singleton or not
        $data = $this->checkBeforeInput($data, 'save');

        // do before save
        // call if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('beforeSave', $data);
        }

        // set column key
        $columnKey = is_null($columnKey) ? $this->primaryKey : $columnKey;

        // if
        if ($data['singleton'])
        {
            // insert single
            $result = $this->builder->upsert($data['sanitizedData'], $columnKey, [$this->createdField, $this->updatedField]);

        } else {

            // insert bulk
            $result = $this->builder->upsertBulk($data['sanitizedData'], $columnKey, [$this->createdField, $this->updatedField]);
        }

        // set data
        $data = [
            'singleton'     => $data['singleton'],
            'result_status' => $result,
        ];

        // do after save
        // call if using callback
        if ($this->useCallbacks)
        {
            $data = $this->triggerCallback('afterSave', $data);
        }

        // return
        return $data;
    }

    //======================================================================================================

    public function delete(): array
    {
        // check where
        $this->checkSoftDeleteOption();

        // do before delete
        // call if using callback
        if ($this->useCallbacks)
        {
            $this->triggerCallback('beforeDelete', [
                'method'      => __FUNCTION__,
                'truncate'    => ($this->useConditional) ? false : true
            ]);
        }

        // generate timestamp
        $time = $this->generateTimestamp();

        // executing
        if ($this->useSoftDelete)
        {
            $result = $this->builder->update([
                $this->deletedField => $time
            ]);

        } else {

            $result = $this->builder->delete();
        }

        // do after delete
        // call if using callback
        if ($this->useCallbacks)
        {
            $result = $this->triggerCallback('afterDelete', $result);
        }

        // return
        return $result;
    }

    //======================================================================================================

    public function purge(): array
    {
        // add so it will still purge
        if ($this->useTimestamps)
        {
            $this->withDeletedOption = true;
        }

        // add soft delete option
        $this->checkSoftDeleteOption();

        // do before delete
        // call if using callback
        if ($this->useCallbacks)
        {
            $this->triggerCallback('beforeDelete', [
                'method'      => __FUNCTION__,
                'truncate'    => ($this->useConditional) ? false : true
            ]);
        }

        // executing
        $result = $this->builder->delete();

        // do after delete
        // call if using callback
        if ($this->useCallbacks)
        {
            $result = $this->triggerCallback('afterDelete', $result);
        }

        // return
        return $result;
    }

    //======================================================================================================

    public function deletedOnly(): Model
    {
        $this->deletedOnlyOption = true;
        return $this;
    }

    //======================================================================================================

    public function withDeleted(): Model
    {
        $this->withDeletedOption = true;
        return $this;
    }

    //======================================================================================================
}