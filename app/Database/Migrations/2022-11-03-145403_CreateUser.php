<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUser extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => '128',
            ],
            'is_admin' => [
                'type' => 'INT',
                'default' => 0
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => '32',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['email']);
        $this->forge->createTable('user');
    }

    public function down()
    {
        $this->forge->dropTable('user');
    }
}
