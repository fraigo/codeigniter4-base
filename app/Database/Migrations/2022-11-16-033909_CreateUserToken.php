<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserToken extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user', [
            'apikey' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
                'null' => true
            ],
            'last_login' => [
                'type'       => 'DATETIME',
                'null' => true
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user','apikey');
        $this->forge->dropColumn('user','last_login');
    }
}
