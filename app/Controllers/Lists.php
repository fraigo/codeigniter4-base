<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Lists extends BaseResourceController
{
    protected $modelName = 'App\Models\Lists';
    protected $format    = 'json';

    protected function actionRules($action, $data=null){

        $name = @$data["name"];
        return [
            "value" => [
                "rules" => 'required|unique_fields[lists,name,value]',
                'errors' => [
                    'unique_fields' => "Value {value} already exists in {$name}"
                ],
            ],
            "name" => "required",
            "label" => "required"
        ];
    }


    public function map(){
        helper('map');
        $results = groupArrayByKey("name",$this->filteredModel()->findAll(),"value","label");
        return $this->result($results);
    }

    public function byName(){
        helper('map');
        $results = groupArrayByKey("name",$this->filteredModel()->orderBy("value")->findAll());
        return $this->result($results);
    }

}


