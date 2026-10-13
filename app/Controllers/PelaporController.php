<?php
namespace App\Controllers;

use App\Models\TiketModel;
use App\Models\FasilitasModel;

class PelaporController extends BaseController
{
    public function index()
    {
        $tiketModel = new TiketModel();
        
        // Ambil tiket yang hanya dilaporkan oleh user yang sedang login
        $pelapor_id = session()->get('id');
        
        $data = [
            'title' => 'Tiket Saya',
            // Kita join dengan tabel fasilitas agar nama fasilitasnya muncul
            'tiket' => $tiketModel->select('tiket_perawatan.*, fasilitas.nama_fasilitas, fasilitas.lokasi')
                                  ->join('fasilitas', 'fasilitas.id = tiket_perawatan.fasilitas_id')
                                  ->where('pelapor_id', $pelapor_id)
                                  ->orderBy('tgl_dibuat', 'DESC')
                                  ->findAll()
        ];
        
        return view('pelapor/dashboard', $data);
    }

    public function buat()
    {
        $fasilitasModel = new FasilitasModel();
        $data = [
            'title'     => 'Buat Laporan Baru',
            'fasilitas' => $fasilitasModel->findAll()
        ];
        return view('pelapor/buat_laporan', $data);
    }

    public function simpan()
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();

        // Mulai Database Transaction (agar jika gagal upload foto, data tiket tidak tersimpan/ter-rollback)
        $db->transStart();

        // 1. Simpan data laporan utama
        $dataTiket = [
            'jenis_tiket'   => 'insidental',
            'judul_laporan' => $this->request->getPost('judul_laporan'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'fasilitas_id'  => $this->request->getPost('fasilitas_id'),
            'pelapor_id'    => session()->get('id'),
            'status'        => 'menunggu_respon',
            'tgl_dibuat'    => date('Y-m-d H:i:s')
        ];
        
        $tiketModel->insert($dataTiket);
        $tiket_id = $tiketModel->getInsertID();

        // 2. Proses Upload Foto (Simulasi sebelum Google Drive)
        $foto = $this->request->getFile('foto_kondisi');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            
            // --- AREA INTEGRASI GOOGLE DRIVE ---
            // Karena Anda sudah familiar dengan Service Account GDrive API,
            // Nantinya baris pemindahan file ini diganti dengan proses pengiriman file ke API,
            // lalu dapatkan $google_file_id dari response-nya.
            
            // Untuk sementara, kita pindahkan ke folder lokal agar bisa dites jalannya:
            $newName = $foto->getRandomName();
            $foto->move('uploads/laporan', $newName);
            
            // Simpan ke tabel tiket_foto
            $db->table('tiket_foto')->insert([
                'tiket_id'       => $tiket_id,
                'tipe_foto'      => 'sebelum',
                'google_file_id' => 'LOKAL_'.$newName, // Nanti ganti dengan ID asli dari GDrive
                'diupload_oleh'  => session()->get('id'),
                'tgl_upload'     => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'Gagal mengirim laporan.');
        } else {
            session()->setFlashdata('success', 'Laporan berhasil dikirim dan sedang menunggu respon Admin.');
        }

        return redirect()->to('/pelapor/dashboard');
    }
}