<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Lists extends BaseResourceController
{
    protected $modelName = 'App\Models\Lists';
    protected $format    = 'json';

    protected function actionRules($action){

        return [
            "name" => $action=="update" ? 'required' : [
                "rules" => 'required|unique_fields[lists,name,value,{name},{value}]',
                'errors' => [
                    'unique_fields' => 'Value already exists for {value}',
                ],
            ],
            "value" => "required",
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


