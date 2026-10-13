<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiketFoto extends Migration
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
            'tipe_foto' => [
                'type'       => 'ENUM',
                'constraint' => ['sebelum', 'sesudah'],
            ],
            'google_file_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '150', // Menyimpan ID file Google Drive
            ],
            'diupload_oleh' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tgl_upload' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tiket_id', 'tiket_perawatan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('diupload_oleh', 'users', 'id', 'CASCADE', 'RESTRICT');
        
        $this->forge->createTable('tiket_foto');
    }

    public function down()
    {
        $this->forge->dropTable('tiket_foto');
    }
}