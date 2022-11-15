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
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
            ],
            'value' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'label' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['name', 'value']);
        $this->forge->createTable('lists');
    }

    public function down()
    {
        $this->forge->dropTable('lists');
    }
}
