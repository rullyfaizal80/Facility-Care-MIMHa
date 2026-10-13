<?php
namespace App\Controllers;

use App\Models\TiketModel;

class CarakaController extends BaseController
{
    public function index()
    {
        $tiketModel = new TiketModel();
        $caraka_id  = session()->get('id');

        // Ambil tiket yang ditugaskan ke caraka ini dan statusnya 'dikerjakan'
        $data = [
            'title' => 'Tugas Hari Ini',
            'tugas' => $tiketModel->select('tiket_perawatan.*, fasilitas.nama_fasilitas, fasilitas.lokasi')
                                  ->join('fasilitas', 'fasilitas.id = tiket_perawatan.fasilitas_id')
                                  ->where('caraka_id', $caraka_id)
                                  ->whereIn('status', ['dikerjakan', 'menunggu_approval'])
                                  ->orderBy('prioritas', 'DESC') // Yang prioritas tinggi di atas
                                  ->orderBy('tgl_dibuat', 'DESC')
                                  ->findAll()
        ];
        
        return view('caraka/dashboard', $data);
    }

    public function kerjakan($tiket_id)
    {
        $tiketModel = new TiketModel();
        
        $data = [
            'title' => 'Kerjakan Tugas',
            'tiket' => $tiketModel->select('tiket_perawatan.*, fasilitas.nama_fasilitas, fasilitas.lokasi')
                                  ->join('fasilitas', 'fasilitas.id = tiket_perawatan.fasilitas_id')
                                  ->where('tiket_perawatan.id', $tiket_id)
                                  ->first()
        ];

        // Jika tiket tidak ditemukan atau bukan milik caraka ini, tolak
        if(empty($data['tiket']) || $data['tiket']['caraka_id'] != session()->get('id')) {
            return redirect()->to('/caraka/dashboard');
        }

        return view('caraka/kerjakan', $data);
    }

    public function selesaikan()
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();
        
        $tiket_id = $this->request->getPost('tiket_id');
        
        $db->transStart();

        // 1. Update status tiket menjadi 'menunggu_approval' agar admin bisa cek
        $tiketModel->update($tiket_id, [
            'status'      => 'menunggu_approval',
            'tgl_selesai' => date('Y-m-d H:i:s')
        ]);

        // 2. Proses Upload Foto Bukti Selesai (Simulasi Lokal)
        $foto = $this->request->getFile('foto_sesudah');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            
            $newName = $foto->getRandomName();
            $foto->move('uploads/laporan', $newName);
            
            // Simpan record foto ke database
            $db->table('tiket_foto')->insert([
                'tiket_id'       => $tiket_id,
                'tipe_foto'      => 'sesudah',
                'google_file_id' => 'LOKAL_'.$newName, // Nanti diubah saat GDrive API dipasang
                'diupload_oleh'  => session()->get('id'),
                'tgl_upload'     => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'Gagal mengirim laporan selesai.');
        } else {
            session()->setFlashdata('success', 'Tugas berhasil diselesaikan. Menunggu persetujuan Admin.');
        }

        return redirect()->to('/caraka/dashboard');
    }

    public function riwayat()
    {
        $tiketModel = new TiketModel();
        $caraka_id  = session()->get('id');

        // Ambil tiket yang sudah 'selesai' atau 'ditolak'
        $data = [
            'title' => 'Riwayat Pekerjaan',
            'tugas' => $tiketModel->select('tiket_perawatan.*, fasilitas.nama_fasilitas, fasilitas.lokasi')
                                  ->join('fasilitas', 'fasilitas.id = tiket_perawatan.fasilitas_id')
                                  ->where('caraka_id', $caraka_id)
                                  ->whereIn('status', ['selesai']) 
                                  ->orderBy('tgl_selesai', 'DESC') // Urutkan dari yang paling baru selesai
                                  ->findAll()
        ];
        
        return view('caraka/riwayat', $data);
    }
}