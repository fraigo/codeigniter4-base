<?php

namespace App\Models;

use CodeIgniter\Model;

class UserOption extends BaseModel
{
    protected $DBGroup          = 'default';
    protected $table            = 'user_option';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ["user_id", "name", "type", "value"];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function createOptions($userId){
        $allOptions = [
            'Language' => 'select', 
            'Timezone' => 'select',
            'Country' => 'select'
        ];
        foreach($allOptions as $opt => $type){
            $data = [
                "user_id" => $userId,
                "name" => $opt,
                "type" => $type,
                "value" => null
            ];
            try{
                $this->insert($data);
            } catch(\Exception $e){
                
            }
        }
    }

    public function getUserOptions($userId){
        $options = $this
            ->select(['name','type','value'])
            ->where('user_id',$userId)
            ->asArray()->find();
        helper('map');
        return mapArrayByKey("name", $options);
    }

}
