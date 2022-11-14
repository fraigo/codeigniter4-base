<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User;

class Auth extends BaseController
{
    public function __construct() {
        $this->model = new User();
    }

    protected function validateRequest($request, $rules){
        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        if (!$validation->run($request)){
            $message = [];
            $errors = $validation->getErrors();
            foreach($errors as $fld=>$err){
                $message[] = $err;
            }
            return [
                "success"=>false,
                "errors"=> $errors,
                "requestdata" => $request,
                "message"=> implode("\n",$message)
            ];
        }
        return null;
    }

    private function getValues($fields=[]){
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

    private function getProfileModel($email){
        return $this->model
            ->select(['name','email','is_admin'])
            ->where("email", $email);
    }

    public function login() {
        $request = $this->getValues();
        if (!$request){
            return $this->response->setStatusCode(401)->setJSON(["success"=>false,"message"=>"Invalid request"]);
        }
        $rules = [
            'password' => 'required',
            'email'    => 'required|valid_email',
        ];
        $errors = $this->validateRequest($request,$rules);
        if ($errors){
            return $this->response->setStatusCode(401)->setJSON($errors);
        }
        $user = $this->getProfileModel($request["email"])
            ->where("password", md5($request["password"]))
            ->first();
        $apikey = null;
        $message = null;
        $session = session();
        if ($user){
            $apikey = md5($request["email"].rand(10000000,99999999));
            $session->set('apikey', $apikey);
            $session->set('user', $user);
        } else {
            return $this->response->setStatusCode(401)->setJSON(["success"=>false,"message"=>"Authentication Failed"]);
        }
        return $this->response->setJSON(["success"=>$user!=null, "user"=>$user, "apikey"=>$apikey]);
    }

    public function logout() {
        $session = session();
        $session->remove('user');
        $session->remove('apikey');
        return $this->response->setJSON(["success"=>true]);
    }

    public function profile(){
        $request = $this->getValues(['name']);
        $rules = [
            'name' => 'required',
        ];
        $errors = $this->validateRequest($request,$rules);
        if ($errors){
            return $this->response->setStatusCode(401)->setJSON($errors);
        }
        $user = $this->getProfileModel(session("user")["email"])
            ->select(['id','email'])
            ->first();
        $this->model->update($user["id"],$request);
        return $this->response->setJSON(["success"=>true,"user"=>$user]);
    }

    public function me(){
        $user = $this->getProfileModel(session("user")["email"])
            ->select(['id'])
            ->first();
        $optionsModel = model('\App\Models\UserOption');
        $optionsModel->createOptions($user["id"]);
        $options = $optionsModel->getUserOptions($user["id"]);
        return $this->response->setJSON([
            "success"=>true,
            "options"=>$options,
            "user"=>$this->getProfileModel(session("user")["email"])->first()
        ]);
    }

    public function options(){
        $user = $this->getProfileModel(session("user")["email"])
            ->select(['id'])
            ->first();
        $request = $this->getValues(['name','type','value']);
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'value' => [
                "rules" => 'required',
                "label" => $request['name'],
            ],
        ];
        $errors = $this->validateRequest($request,$rules);
        if ($errors){
            return $this->response->setStatusCode(401)->setJSON($errors);
        }
        $optModel = model('\App\Models\UserOption');
        $opt = $optModel
            ->select(['id'])
            ->where('user_id',$user["id"])
            ->where('name',$request["name"])
            ->first();
        if ($opt){
            $optModel->update($opt["id"],$request);
            return $this->response->setJSON(["success"=>true,"mode"=>'update']);    
        } else{
            return $this->response->setJSON(["success"=>false,"message"=>"Not found"]);
        }
    }


    public function password(){
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
        $errors = $this->validateRequest($request,$rules);
        if ($errors){
            return $this->response->setStatusCode(401)->setJSON($errors);
        }
        $user = $this->getProfileModel(session("user")["email"])
            ->select(['id','email'])
            ->first();
        $request["password"] = md5($request["password"]);
        $this->model->update($user["id"],$request);
        return $this->response->setJSON(["success"=>true]);
    }
}
