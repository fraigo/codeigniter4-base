<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User;

class Auth extends BaseController
{
    public function __construct() {
        $this->model = new User();
    }

    public function login() {
        $request = $this->request->getJSON(true);
        if (!$request){
            return $this->response->setStatusCode(401)->setJSON(["success"=>false,"message"=>"Invalid request"]);
        }
        $rules = [
            'password' => 'required',
            'email'    => 'required|valid_email',
        ];
        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        if (!$validation->run($request)){
            $message = [];
            $errors = $validation->getErrors();
            foreach($errors as $fld=>$err){
                $message[] = $err;
            }
            return $this->response->setStatusCode(401)->setJSON([
                "success"=>false,
                "errors"=> $errors,
                "message"=> implode("\n",$message)
            ]);
        }
        $user = $this->model
            ->select(['name','email'])
            ->where("email", $request["email"])
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
}
