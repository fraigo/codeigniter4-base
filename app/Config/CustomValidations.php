<?php

namespace Config;

class CustomValidations {

    public function unique_fields($value, $params){
        @list($table,$field1,$field2,$value1,$value2) = explode(',',$params);
        $db = \Config\Database::connect();
        $result = $db->table($table)->where($field1,$value1)->where($field2,$value2)->get()->getResult();
        return $result ? false : true;
    }

}