<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class FasilitasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nama_fasilitas' => 'AC Panasonic 1 PK',
                'lokasi'         => 'Ruang Guru Lantai 1',
                'kategori'       => 'Elektronik',
            ],
            [
                'nama_fasilitas' => 'Proyektor Epson',
                'lokasi'         => 'Ruang Kelas X-A',
                'kategori'       => 'Elektronik',
            ],
            [
                'nama_fasilitas' => 'Pompa Air Shimizu',
                'lokasi'         => 'Gedung Utama Belakang',
                'kategori'       => 'Plumbing',
            ],
            [
                'nama_fasilitas' => 'Wastafel',
                'lokasi'         => 'Toilet Siswa Lantai 2',
                'kategori'       => 'Sanitasi',
            ],
            [
                'nama_fasilitas' => 'Meja Guru Utama',
                'lokasi'         => 'Ruang Kelas XI-B',
                'kategori'       => 'Mebel',
            ]
        ];

        // Insert semua data ke tabel fasilitas
        $this->db->table('fasilitas')->insertBatch($data);
    }
}