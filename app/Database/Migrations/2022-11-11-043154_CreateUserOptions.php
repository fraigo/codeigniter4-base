<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserOptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => '64',
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 32,
                'default' => 'text',
            ],
            'value' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'name']);
        $this->forge->createTable('user_option');
    }

    public function down()
    {
        $this->forge->dropTable('user_option');
    }
}
