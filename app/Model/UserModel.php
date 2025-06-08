<?php namespace App\Model;

use Aether\Model;

class UserModel extends Model
{
    // table description
    protected string $table = 'user';
    protected string $primaryKey = 'id';

    // insert/update/upsert option
    protected array $allowedFields = ['name', 'age', 'status'];
    protected bool $allowEmptyInsert = false;
    
    // timestamps
    protected bool $useTimestamps = true;
    protected string $dateFormat = 'datetime'; // datetime, date, or just time
    protected string $createdField = 'created_at';
    protected string $updatedField = 'updated_at';

    protected bool $useSoftDelete = true;
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