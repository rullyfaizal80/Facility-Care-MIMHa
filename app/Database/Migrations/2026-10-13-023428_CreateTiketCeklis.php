<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiketCeklis extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tiket_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'item_tugas' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'is_done' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0, // 0 = belum dicentang, 1 = sudah dicentang
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tiket_id', 'tiket_perawatan', 'id', 'CASCADE', 'CASCADE');
        
        $this->forge->createTable('tiket_ceklis');
    }

    public function down()
    {
        $this->forge->dropTable('tiket_ceklis');
    }
}