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
        $data = [
            'id' => 1002,
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => md5("user"),
            'is_admin' => 0
        ];
        $this->db->table('user')->insert($data);
    }
}
