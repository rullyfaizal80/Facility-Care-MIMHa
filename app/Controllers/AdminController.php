<?php
namespace App\Controllers;

use App\Models\TiketModel;
use App\Models\UserModel;
use App\Models\FasilitasModel;

class AdminController extends BaseController
{
    public function index()
    {
        $tiketModel = new TiketModel();
        
        // Menghitung statistik untuk Dashboard
        $data = [
            'title'         => 'Dashboard Admin',
            'tiket_masuk'   => $tiketModel->where('status', 'menunggu_respon')->countAllResults(),
            'tiket_proses'  => $tiketModel->where('status', 'dikerjakan')->countAllResults(),
            'tiket_selesai' => $tiketModel->where('status', 'selesai')->countAllResults(),
        ];
        
        return view('admin/dashboard', $data);
    }

    // app/Controllers/AdminController.php

   public function kelolaTiket()
    {
        $tiketModel = new TiketModel();
        $userModel  = new UserModel();
        $db = \Config\Database::connect();

        $tiket_raw = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                                ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                                ->orderBy('tgl_dibuat', 'DESC')
                                ->findAll();

        // Siapkan array semua Caraka untuk form penugasan modal
        $list_semua_caraka = $userModel->where('role', 'caraka')->where('is_active', 1)->findAll();

        $tiket_lengkap = [];
        foreach ($tiket_raw as $t) {
            // Ambil foto sebelum
            $foto = $db->table('tiket_foto')->where('tiket_id', $t['id'])->where('tipe_foto', 'sebelum')->orderBy('id', 'DESC')->get()->getRowArray();
            $t['foto_sebelum'] = $foto ? $foto['google_file_id'] : null;
            
            // --- LOGIKA MENGAMBIL NAMA MULTI-CARAKA ---
            $t['nama_caraka'] = '-'; // Default jika kosong
            
            if (!empty($t['caraka_id'])) {
                // Ubah string "1,3" jadi array [1, 3]
                $caraka_ids = explode(',', $t['caraka_id']); 
                
                // Cari nama lengkap user berdasarkan ID-ID tersebut
                $caraka_users = $userModel->whereIn('id', $caraka_ids)->findAll();
                
                // Kumpulkan nama-namanya
                $nama_nama = [];
                foreach ($caraka_users as $cu) {
                    $nama_nama[] = $cu['nama_lengkap'];
                }
                
                // Gabungkan kembali jadi string dengan koma: "Pak Joko, Pak Santoso"
                $t['nama_caraka'] = implode(', ', $nama_nama);
            }
            // ------------------------------------------

            $tiket_lengkap[] = $t;
        }

        $data = [
            'title'  => 'Kelola Tiket Masuk',
            'tiket'  => $tiket_lengkap, 
            'caraka' => $list_semua_caraka
        ];
        
        return view('admin/kelola_tiket', $data);
    }

    public function tugaskan()
    {
        $tiketModel = new TiketModel();
        
        $tiket_id  = $this->request->getPost('tiket_id');
        $prioritas = $this->request->getPost('prioritas');
        $tgl_tugas = $this->request->getPost('tgl_dijadwalkan'); // Tanggal penugasan
        
        // Menangkap Multi-Caraka (Karena bentuknya array dari form multiple select)
        $caraka_array = $this->request->getPost('caraka_id'); 
        
        // Ubah array [1, 3] menjadi string koma "1,3" agar bisa masuk ke VARCHAR
        $caraka_string = implode(',', (array)$caraka_array); 

        $tiketModel->update($tiket_id, [
            'caraka_id'       => $caraka_string, // Bisa 1 orang atau banyak
            'prioritas'       => $prioritas,
            'tgl_dijadwalkan' => $tgl_tugas,     // Menyimpan tanggal penugasan
            'status'          => 'dikerjakan'
        ]);

        session()->setFlashdata('success', 'Tiket berhasil ditugaskan ke Caraka.');
        return redirect()->to('/admin/tiket');
    }

    public function detailTiket($tiket_id)
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();

        // 1. Ambil data utama tiket
        $tiket = $tiketModel->select('tiket_perawatan.*, fasilitas.nama_fasilitas, fasilitas.lokasi, users.nama_lengkap as nama_pelapor, caraka.nama_lengkap as nama_caraka')
                            ->join('fasilitas', 'fasilitas.id = tiket_perawatan.fasilitas_id')
                            ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                            ->join('users as caraka', 'caraka.id = tiket_perawatan.caraka_id', 'left')
                            ->where('tiket_perawatan.id', $tiket_id)
                            ->first();

        if (empty($tiket)) {
            return redirect()->to('/admin/tiket');
        }

        // 2. Ambil data foto (Sebelum & Sesudah)
        // Jika pakai GDrive, nama file yang diambil adalah ID GDrive, jika pakai lokal, adalah nama file lokalnya
        $foto_sebelum = $db->table('tiket_foto')
                           ->where('tiket_id', $tiket_id)
                           ->where('tipe_foto', 'sebelum')
                           ->orderBy('id', 'DESC') // Ambil yang paling baru
                           ->get()->getRowArray();
                           
        $foto_sesudah = $db->table('tiket_foto')
                           ->where('tiket_id', $tiket_id)
                           ->where('tipe_foto', 'sesudah')
                           ->orderBy('id', 'DESC')
                           ->get()->getRowArray();

        $data = [
            'title'        => 'Detail & Approval Tiket',
            'tiket'        => $tiket,
            'foto_sebelum' => $foto_sebelum,
            'foto_sesudah' => $foto_sesudah
        ];

        return view('admin/detail_tiket', $data);
    }

    public function prosesApproval()
    {
        $tiketModel = new TiketModel();
        
        $tiket_id = $this->request->getPost('tiket_id');
        $aksi     = $this->request->getPost('aksi'); 
        $catatan  = $this->request->getPost('catatan_admin'); // Tangkap inputan textarea dari form

        if ($aksi == 'approve') {
            $tiketModel->update($tiket_id, [
                'status'        => 'selesai',
                'catatan_admin' => $catatan // Simpan catatan
            ]);
            session()->setFlashdata('success', 'Laporan berhasil di-Approve (Selesai).');
        } else {
            $tiketModel->update($tiket_id, [
                'status'        => 'dikerjakan', // Kembalikan statusnya ke caraka
                'catatan_admin' => $catatan // Simpan alasan penolakan
            ]);
            session()->setFlashdata('error', 'Laporan ditolak dan dikembalikan ke Caraka.');
        }

        return redirect()->to('/admin/tiket');
    }
}