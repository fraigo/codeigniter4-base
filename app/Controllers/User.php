<?php

namespace App\Controllers;

class User extends BaseResourceController
{
    protected $modelName = 'App\Models\User';
    protected $format    = 'json';

    protected function selectFields(){
        return ["id","name","email","is_admin"];
    }

    protected function filterModel($model){
        if (!$this->is_admin()){
            $model->where('is_admin',0);
        }
        return $model;
    }

    protected function restrictedActions(){
        if (!$this->is_admin()){
            return ["create","delete"];
        }
        return [];
    }

    protected function addAllowedFields($action){
        if ($this->is_admin()){
            $this->model->addAllowedFields(["is_admin"]);
        }
    }

    protected function actionRules($action){
        $rules = [
            'name' => 'required',
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
            'is_admin' => 'if_exist|in_list[0,1]',
        ];
        if ($action=="update"){
            $rules["password"]='if_exist|min_length[6]';
        }
        return $rules;
    }

    protected function formatRequest($request){
        if (!empty(@$request["password"])){
            $request["password"] = md5($request["password"]);
        }
        return $request;
    }


    public function delete($id=null){
        $item = $this->getById($id);
        $user = session("user");
        if ($item["email"]==$user["email"]){
            return $this->error(["Cannot delete yourself"]);
        }
        return parent::delete($id);
    }

}
