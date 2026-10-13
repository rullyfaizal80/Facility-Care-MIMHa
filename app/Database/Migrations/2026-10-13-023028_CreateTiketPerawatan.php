<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiketPerawatan extends Migration
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
            'jenis_tiket' => [
                'type'       => 'ENUM',
                'constraint' => ['insidental', 'rutin'],
            ],
            'judul_laporan' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fasilitas_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'pelapor_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => true, // Null jika tiket dibuat oleh admin (rutin)
            ],
            'caraka_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => true, // Null saat baru dibuat pelapor
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['menunggu_respon', 'dikerjakan', 'menunggu_approval', 'selesai', 'ditolak'],
                'default'    => 'menunggu_respon',
            ],
            'prioritas' => [
                'type'       => 'ENUM',
                'constraint' => ['rendah', 'sedang', 'tinggi'],
                'null'       => true,
            ],
            'tgl_dibuat' => [
                'type' => 'DATETIME',
            ],
            'tgl_dijadwalkan' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tgl_selesai' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        
        // Pembuatan Foreign Key constraint
        $this->forge->addForeignKey('fasilitas_id', 'fasilitas', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('pelapor_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->addForeignKey('caraka_id', 'users', 'id', 'CASCADE', 'SET NULL');
        
        $this->forge->createTable('tiket_perawatan');
    }

    public function down()
    {
        $this->forge->dropTable('tiket_perawatan');
    }
}