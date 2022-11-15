<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Lists extends BaseResourceController
{
    protected $modelName = 'App\Models\Lists';
    protected $format    = 'json';


    public function map(){
        helper('map');
        $results = groupArrayByKey("name",$this->filteredModel()->findAll(),"value","label");
        return $this->result($results);
    }

    public function byName(){
        helper('map');
        $results = groupArrayByKey("name",$this->filteredModel()->findAll());
        return $this->result($results);
    }

}


