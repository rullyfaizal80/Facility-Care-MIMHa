<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class UbahInputFasilitasManual extends Migration
{
    public function up()
    {
        // 1. Tambah kolom teks untuk input manual
        $this->forge->addColumn('tiket_perawatan', [
            'lokasi_manual' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'judul_laporan'
            ]
        ]);

        // 2. (Opsional) Boleh hapus Foreign Key fasilitas_id jika Anda sudah tidak pakai tabel fasilitas sama sekali
        // $this->forge->dropForeignKey('tiket_perawatan', 'tiket_perawatan_fasilitas_id_foreign');
        // $this->forge->dropColumn('tiket_perawatan', 'fasilitas_id');
    }

    public function down()
    {
        $this->forge->dropColumn('tiket_perawatan', 'lokasi_manual');
    }
}