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
        return $this->model->find($id);
    }

    public function index()
    {
        $user = session('user');
        return $this->result($this->model->findAll());
    }

    public function show($id = null){
        return $this->result($this->getById($id));
    }

    public function create($id = null){
        $user = session('user');
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
            $this->model->find($last_id),
            $result?true:false);
    }

    public function update($id = null){
        $item = $this->getById($id);
        if (!$item){
            return $this->error(["Not found"]);
        }
        $request = $this->request->getJSON(true);
        $user = session('user');
        if ($item["password"]!=$request["password"]){
            $request["password"] = md5($request["password"]);
        }
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
        $result = $this->model->update($id,$request);
        return $this->result(
            $this->model->find($id),
            $result?true:false);
    }

    public function delete($id = null){
        $user = session('user');
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
