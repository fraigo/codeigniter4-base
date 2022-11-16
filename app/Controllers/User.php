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
        if (!$item){
            return $this->error(["Not found"],404);
        }
        $user = $this->authUser;
        if ($item["email"]==$user["email"]){
            return $this->error(["Cannot delete yourself"]);
        }
        return parent::delete($id);
    }

    public function profile($id, $profile=false){
        if (!$profile && !$this->is_admin()){
            return $this->error("Action not available",401);
        }
        $optionsModel = model('\App\Models\UserOption');
        $optionsModel->createOptions($id);
        $options = $optionsModel->getUserOptions($id);
        return $this->response->setJSON([
            "success"=>true,
            "options"=>$options,
            "user"=>$this->getById($id)
        ]);
    }

    public function updateProfile($id, $profile=false){
        if (!$profile && !$this->is_admin()){
            return $this->error("Action not available",401);
        }
        $request = $this->getValues(['name']);
        if (!$profile){
            $request = $this->getValues(['name','is_admin']);
            $this->addAllowedFields("update");
        }
        $rules = [
            'name' => 'required',
        ];
        $errors = $this->validateWithRules($request,$rules);
        if ($errors){
            return $this->response->setStatusCode(401)->setJSON($errors);
        }
        $this->model->update($id,$request);
        return $this->response->setJSON(["success"=>true,"data"=>$request]);
    }

    public function options($id, $profile=false){
        if (!$profile && !$this->is_admin()){
            return $this->error("Action not available",401);
        }
        $request = $this->getValues(['name','type','value']);
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'value' => [
                "rules" => 'required',
                "label" => $request['name'],
            ],
        ];
        $errors = $this->validateWithRules($request,$rules);
        if ($errors){
            return $this->response->setStatusCode(401)->setJSON($errors);
        }
        $optModel = model('\App\Models\UserOption');
        $opt = $optModel
            ->select(['id'])
            ->where('user_id',$id)
            ->where('name',$request["name"])
            ->first();
        if ($opt){
            $optModel->update($opt["id"],$request);
            return $this->response->setJSON(["success"=>true,"mode"=>'update',"id"=>$opt["id"],"data"=>$request]);    
        } else{
            return $this->response->setJSON(["success"=>false,"message"=>"Not found"]);
        }
    }

    public function password($id, $profile=false){
        if (!$profile && !$this->is_admin()){
            return $this->error("Action not available",401);
        }
        $request = $this->getValues(['password','password1']);
        $rules = [
            'password' => 'required',
            'password1' => [
                'label'  => 'Repeat password',
                'rules'  => 'required|matches[password]',
                'errors' => [
                ],
            ],
        ];
        $errors = $this->validateWithRules($request,$rules);
        if ($errors){
            return $this->error($errors);
        }
        $request["password"] = md5($request["password"]);
        $this->model->update($id,$request);
        return $this->response->setJSON(["success"=>true]);
    }

}
