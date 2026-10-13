<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCatatanAdminToTiket extends Migration
{
    public function up()
    {
        // Mendefinisikan kolom baru yang akan ditambahkan
        $fields = [
            'catatan_admin' => [
                'type' => 'TEXT',
                'null' => true, // Boleh kosong (karena opsional saat disetujui)
                'after' => 'status' // (Opsional) meletakkan kolom ini setelah kolom status
            ],
        ];

        // Menambahkan kolom ke tabel tiket_perawatan
        $this->forge->addColumn('tiket_perawatan', $fields);
    }

    public function down()
    {
        // Jika kita melakukan rollback, kolom ini akan dihapus
        $this->forge->dropColumn('tiket_perawatan', 'catatan_admin');
    }
}