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

    public function getProfileModel($apikey=null){
        $apikey = $apikey?:$this->getApiKey();
        return $this->model
            ->select(['name','email','is_admin'])
            ->where("apikey", $apikey?:'');
    }

    private function getApiKey(){
        $apiKey = @$_GET["APIKEY"]?:(@$_SERVER["HTTP_X_APIKEY"]?:session('apikey'));
        return $apiKey;
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
        $user = $this->model
            ->select(['id','apikey'])
            ->where('email',$request["email"])
            ->where("password", md5($request["password"]))
            ->first();
        $apikey = null;
        $message = null;
        $session = session();
        if ($user){
            $apikey = $user["apikey"]=='' ? md5($request["email"].rand(10000000,99999999)) : $user["apikey"];
            $session->set('apikey', $apikey);
            $session->set('user', $user);
            $this->model->addAllowedFields(['last_login','apikey']);
            $this->model->update($user["id"],[
                'last_login' => date("Y-m-d H:i:s"),
                'apikey' => $apikey
            ]);

        } else {
            return $this->response->setStatusCode(401)->setJSON(["success"=>false,"message"=>"Authentication Failed"]);
        }
        return $this->response->setJSON(["success"=>$user!=null, "user"=>$this->getProfileModel($apikey)->first(), "apikey"=>$apikey]);
    }

    public function logout() {
        $session = session();
        $session->remove('user');
        $session->remove('apikey');
        return $this->response->setJSON(["success"=>true]);
    }

    public function profile(){
        $user = $this->getProfileModel()
            ->select(['id'])
            ->first();
        $userController = new \App\Controllers\User();
        $userController->initController($this->request, $this->response, $this->logger);
        return $userController->updateProfile($user["id"], true);
    }

    public function me(){
        $user = $this->getProfileModel()
            ->select(['id'])
            ->first();
        if (!$user){
            return $this->response->setStatusCode(401)->setJSON(["success"=>false,"message"=>"Authentication Failed"]);
        }
        $userController = new \App\Controllers\User();
        $userController->initController($this->request, $this->response, $this->logger);
        return $userController->profile($user["id"], true);
    }

    public function options(){
        $user = $this->getProfileModel()
            ->select(['id'])
            ->first();
        $userController = new \App\Controllers\User();
        $userController->initController($this->request, $this->response, $this->logger);
        return $userController->options($user["id"], true);
    }


    public function password(){
        $user = $this->getProfileModel()
            ->select(['id'])
            ->first();
        $userController = new \App\Controllers\User();
        $userController->initController($this->request, $this->response, $this->logger);
        return $userController->password($user["id"], true);
    }
}
