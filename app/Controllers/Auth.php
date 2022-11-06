<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\User;

class Auth extends ResourceController
{
    public function __construct() {
        $this->model = new User();
    }

    public function login() {
        $request = $this->request->getJSON(true);
        if (!$request){
            return $this->response->setStatusCode(401)->setJSON(["success"=>false,"message"=>"Invalid request"]);
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
        return $this->respond(["success"=>$user!=null, "user"=>$user, "apikey"=>$apikey]);
    }

    public function logout() {
        $session = session();
        $session->remove('user');
        $session->remove('apikey');
        return $this->respond(["success"=>true]);
    }
}
