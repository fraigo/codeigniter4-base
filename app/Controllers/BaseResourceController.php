<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class BaseResourceController extends ResourceController
{
    protected function result($data, $success=true){
        return $this->respond([
            "success" => $success,
            "data" => $data
        ]);
    }

    protected function is_admin(){
        $user = session('user');
        return $user['is_admin'];
    }

    protected function error($errors=[],$code=400){
        $messages = [];
        if (!is_array($errors)){
            $errors = [$errors];
        }
        foreach($errors as $fld=>$err){
            $messages[] = $err;
        }
        return $this->response->setStatusCode($code)->setJSON([
            "success" => false,
            "data" => null,
            "errors" => $errors,
            "message" => implode("\n",$messages)
        ]);
    }

    protected function validateWithRules($data,$rules){
        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        if (!$validation->run($data)){
            return $validation->getErrors();
        }
        return null;
    }

    protected function getValues($fields=[]){
        $request = $this->request->getJSON(true);
        if ($fields && count($fields)){
            $values = [];
            foreach($fields as $fld){
                $values[$fld] = @$request[$fld];
            }
        } else {
            $values = $request;
        }
        return $values;
    }

    protected function getById($id){
        return $this->filteredModel()->find($id);
    }

    protected function selectFields(){
        return [];
    }

    protected function filterModel($model){
        return $model;
    }

    protected function filteredModel(){
        return $this->filterModel($this->model->select($this->selectFields()));
    }

    protected function restricted($action){
        return in_array($action,$this->restrictedActions());
    }

    protected function restrictedActions(){
        return [];
    }

    protected function actionRules($action){
        return [];
    }

    protected function formatRequest($request){
        return $request;
    }

    protected function addAllowedFields($action){
        
    }

    public function index()
    {
        return $this->result($this->filteredModel()->findAll());
    }

    public function show($id = null){
        $item = $this->getById($id);
        if (!$item){
            return $this->error(["Not found"]);
        }
        return $this->result($item);
    }

    public function create($id = null){
        if ($this->restricted("create")){
            return $this->error(["Not allowed to create"],401);
        }
        $this->addAllowedFields("create");
        $request = $this->request->getJSON(true);
        $rules = $this->actionRules("create");
        $errors = $this->validateWithRules($request,$rules);
        if ($errors){
            return $this->error($errors);
        }
        $request = $this->formatRequest($request);
        $result = $this->model->insert($request);
        $last_id = $this->model->getInsertID();
        return $this->result(
            $this->getById($last_id),
            $result?true:false
        );
    }

    public function update($id = null){
        if ($this->restricted("update")){
            return $this->error(["Not allowed to update"],401);
        }
        $item = $this->getById($id);
        if (!$item){
            return $this->error(["Not found"]);
        }
        $request = $this->request->getJSON(true);
        $this->addAllowedFields("update");
        $rules = $this->actionRules("update");
        $errors = $this->validateWithRules($request,$rules);
        if ($errors){
            return $this->error($errors);
        }
        $request = $this->formatRequest($request);
        $result = $this->model->update($id,$request);
        return $this->result(
            $this->getById($id),
            $result?true:false);
    }

    public function delete($id = null){
        if ($this->restricted("delete")){
            return $this->error(["Not allowed to delete"],401);
        }
        $item = $this->getById($id);
        if (!$item){
            return $this->error(["Not found"]);
        }
        $result = $this->model->delete($id);
        return $this->result([
            "id" => $id
            ], $result?true:false);
    }


}