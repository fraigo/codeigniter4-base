<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class User extends Seeder
{
    public function run()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => md5("mypassword")
        ];

        // Using Query Builder
        $this->db->table('user')->insert($data);
    }
}
