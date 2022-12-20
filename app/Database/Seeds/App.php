<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class App extends Seeder
{
    public function run()
    {
        $db = new \Config\Database();
        (new User($db))->run();
        (new Lists($db))->run();
    }
}
