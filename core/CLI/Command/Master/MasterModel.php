<?php namespace Aether\CLI\Command\Master;

use Aether\Model;

class MasterModel extends Model
{
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
}