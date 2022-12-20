<?php

namespace Config;

class CustomValidations {

    public function unique_fields($value, $params, $data){
        @list($table,$field1,$field2,$idField) = explode(',',$params);
        $value1 = @$data[$field1];
        $value2 = @$data[$field2];
        $id = @$data[$idField?:"id"];
        $db = \Config\Database::connect();
        $result = $db->table($table)->where($field1,$value1)->where($field2,$value2)->get()->getResult();
        return $result ? $result[0]->id == $id : true;
    }

}