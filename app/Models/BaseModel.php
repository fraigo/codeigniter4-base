<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    function addAllowedFields($fields=[]){
        $this->allowedFields = array_merge($this->allowedFields,$fields);
    }
}
