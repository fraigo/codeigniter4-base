<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class User extends ResourceController
{
    protected $modelName = 'App\Models\User';
    protected $format    = 'json';

    public function result($data, $success=true){
        return $this->respond([
            "success" => $success,
            "data" => $data
        ]);
    }

    public function error($errors=[],$code=400){
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
            "message" => implode('\n',$messages)
        ]);
    }

    private function getById($id){
        $user = session('user');
        if ($user['is_admin']){
            return $this->model->select(["id","name","email","is_admin"])->find($id);
        } else {
            return $this->model->select(["id","name","email","is_admin"])->where('is_admin',0)->find($id);
        }
    }

    public function index()
    {
        $user = session('user');
        if ($user['is_admin']){
            return $this->result($this->model->select(["id","name","email","is_admin"])->findAll());
        } else {
            return $this->result($this->model->select(["id","name","email","is_admin"])->where('is_admin',0)->findAll());
        }
    }

    public function show($id = null){
        return $this->result($this->getById($id));
    }

    public function create($id = null){
        $user = session('user');
        if (!$user['is_admin']){
            return $this->error(["Not allowed to create"],401);
        }else{
            $this->model->addAllowedFields(["is_admin"]);
        }
        $request = $this->request->getJSON(true);
        $rules = [
            'name' => 'required',
            'password' => 'required',
            'email'    => 'required|valid_email',
        ];
        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        if (!$validation->run($request)){
            $errors = $validation->getErrors();
            return $this->error($errors);
        }
        $request["password"] = md5($request["password"]);
        $result = $this->model->insert($request);
        $last_id = $this->model->getInsertID();
        return $this->result(
            $this->getById($last_id),
            $result?true:false);
    }

    public function update($id = null){
        $item = $this->getById($id);
        if (!$item){
            return $this->error(["Not found"]);
        }
        $request = $this->request->getJSON(true);
        $user = session('user');
        if (!$user['is_admin']){
            if ($item["email"]!=$user["email"]){
                return $this->error(["Cannot modify other users"]);
            }
            if ($request["email"] != $item["email"]){
                return $this->error(["Cannot modify email"]);
            }
        }else{
            $this->model->addAllowedFields(["is_admin"]);
        }
        $rules = [
            'name' => 'required',
            'email'    => 'required|valid_email',
        ];
        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        if (!$validation->run($request)){
            $errors = $validation->getErrors();
            return $this->error($errors);
        }
        if (!empty(@$request["password"])){
            $request["password"] = md5($request["password"]);
        }
        $result = $this->model->update($id,$request);
        return $this->result(
            $this->getById($id),
            $result?true:false);
    }

    public function delete($id = null){
        $user = session('user');
        if (!$user['is_admin']){
            return $this->error("Not allowed to delete",401);
        }
        $item = $this->getById($id);
        if (!$item){
            return $this->error(["Not found"]);
        }
        if ($item["email"]==$user["email"]){
            return $this->error(["Cannot delete yourself"]);
        }
        $result = $this->model->delete($id);
        return $this->result([
            "id" => $id
            ], $result?true:false);
    }

}
