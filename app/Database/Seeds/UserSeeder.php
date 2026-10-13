<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username'     => 'admin',
                'password'     => password_hash('123456', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Administrator Sistem',
                'role'         => 'admin',
                'unit_id'      => null,
                'is_active'    => 1,
            ],
            [
                'username'     => 'pimpinan',
                'password'     => password_hash('123456', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Bapak Kepala Sekolah',
                'role'         => 'pimpinan',
                'unit_id'      => null,
                'is_active'    => 1,
            ],
            [
                'username'     => 'pelapor',
                'password'     => password_hash('123456', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Bapak Guru Budi',
                'role'         => 'pelapor',
                'unit_id'      => 1, // Anggap 1 adalah ID Unit Kurikulum/Kelas
                'is_active'    => 1,
            ],
            [
                'username'     => 'caraka1',
                'password'     => password_hash('123456', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Pak Joko (Teknisi)',
                'role'         => 'caraka',
                'unit_id'      => null,
                'is_active'    => 1,
            ],
            [
                'username'     => 'caraka2',
                'password'     => password_hash('123456', PASSWORD_DEFAULT),
                'nama_lengkap' => 'Pak Santoso (Teknisi)',
                'role'         => 'caraka',
                'unit_id'      => null,
                'is_active'    => 1,
            ]
        ];

        // Insert semua data ke tabel users
        $this->db->table('users')->insertBatch($data);
    }
}