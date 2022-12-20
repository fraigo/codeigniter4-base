<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class User extends Seeder
{
    public function run()
    {
        // Clear data
        $this->db->table('user_option')->truncate();
        $this->db->table('user')->truncate();

        $data = [
            'id' => 1001,
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => md5("admin"),
            'is_admin' => 1
        ];
        $this->db->table('user')->insert($data);
        $userOpt = [
            "user_id" => 1001, 
            "name" => 'Language', 
            "type" => 'select', 
            "value" => 'en'
        ];
        $this->db->table('user_option')->insert($userOpt);
        $data = [
            'id' => 1002,
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => md5("user"),
            'is_admin' => 0
        ];
        $this->db->table('user')->insert($data);
        $userOpt = [
            "user_id" => 1002, 
            "name" => 'Language', 
            "type" => 'select', 
            "value" => 'en'
        ];
        $this->db->table('user_option')->insert($userOpt);
    }
}
