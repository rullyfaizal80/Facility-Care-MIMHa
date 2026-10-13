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

    public function kelolaTiket()
    {
        $tiketModel = new TiketModel();
        $userModel  = new UserModel();

        $data = [
            'title'  => 'Kelola Tiket Masuk',
            // Ambil semua tiket, join dengan fasilitas dan users (untuk tahu siapa pelapornya)
            'tiket'  => $tiketModel->select('tiket_perawatan.*, fasilitas.nama_fasilitas, fasilitas.lokasi, users.nama_lengkap as nama_pelapor')
                                   ->join('fasilitas', 'fasilitas.id = tiket_perawatan.fasilitas_id')
                                   ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                                   ->orderBy('tgl_dibuat', 'DESC')
                                   ->findAll(),
            // Ambil daftar user yang memiliki role caraka untuk ditampilkan di dropdown penugasan
            'caraka' => $userModel->where('role', 'caraka')->where('is_active', 1)->findAll()
        ];
        
        return view('admin/kelola_tiket', $data);
    }

    public function tugaskan()
    {
        $tiketModel = new TiketModel();
        
        $tiket_id  = $this->request->getPost('tiket_id');
        $caraka_id = $this->request->getPost('caraka_id');
        $prioritas = $this->request->getPost('prioritas');

        // Update status tiket menjadi dikerjakan dan masukkan ID Caraka
        $tiketModel->update($tiket_id, [
            'caraka_id' => $caraka_id,
            'prioritas' => $prioritas,
            'status'    => 'dikerjakan'
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