<?php
namespace App\Controllers;

use App\Models\TiketModel;

class CarakaController extends BaseController
{
    public function index()
    {
        $tiketModel = new TiketModel();
        $caraka_id  = session()->get('id');
        $hari_ini   = date('Y-m-d'); // Dapatkan tanggal hari ini

        // Menggunakan FIND_IN_SET agar cocok untuk Multi-Caraka VARCHAR('1,3')
        // Filter agar HANYA menampilkan tugas yang jadwalnya HARI INI (atau sebelumnya yang belum selesai)
        $data = [
            'title' => 'Tugas Hari Ini',
            'tugas' => $tiketModel->select('tiket_perawatan.*')
                                  ->where("FIND_IN_SET('$caraka_id', caraka_id) > 0")
                                  ->whereIn('status', ['dikerjakan', 'menunggu_approval'])
                                  // Kondisi: Tampilkan jika tgl_dijadwalkan adalah hari ini ATAU kurang dari hari ini
                                  ->where('DATE(tgl_dijadwalkan) <=', $hari_ini) 
                                  ->orderBy('prioritas', 'DESC') 
                                  ->orderBy('tgl_dibuat', 'DESC')
                                  ->findAll()
        ];
        
        return view('caraka/dashboard', $data);
    }

    // app/Controllers/CarakaController.php

    public function kerjakan($tiket_id)
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect(); // Tambahkan koneksi DB untuk query builder
        $caraka_id  = session()->get('id');
        
        $tiket = $tiketModel->select('tiket_perawatan.*')
                            ->where('id', $tiket_id)
                            ->first();

        // Validasi: Cek apakah tiket ada DAN caraka ini berhak mengerjakannya (FIND_IN_SET)
        if(empty($tiket)) {
            return redirect()->to('/caraka/dashboard');
        }

        $allowed_carakas = explode(',', $tiket['caraka_id']);
        if(!in_array($caraka_id, $allowed_carakas)) {
            return redirect()->to('/caraka/dashboard');
        }

        // Ambil foto pelaporan awal (tipe: sebelum)
        $foto_sebelum = $db->table('tiket_foto')
                           ->where('tiket_id', $tiket_id)
                           ->where('tipe_foto', 'sebelum')
                           ->orderBy('id', 'DESC')
                           ->get()->getRowArray();

        $data = [
            'title'        => 'Kerjakan Tugas',
            'tiket'        => $tiket,
            'foto_sebelum' => $foto_sebelum // Kirim data foto ke view
        ];

        return view('caraka/kerjakan', $data);
    }

    public function selesaikan()
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();
        
        $tiket_id = $this->request->getPost('tiket_id');

        // 1. VALIDASI FILE FOTO (Maksimal 5 MB = 5120 KB)
        $aturanValidasi = [
            'foto_sesudah' => [
                'rules'  => 'uploaded[foto_sesudah]|is_image[foto_sesudah]|max_size[foto_sesudah,5120]',
                'errors' => [
                    'uploaded' => 'Foto hasil perbaikan wajib diunggah!',
                    'is_image' => 'File yang diunggah harus berupa foto/gambar (JPG/PNG).',
                    'max_size' => 'Ukuran foto terlalu besar. Maksimal 5 MB.'
                ]
            ]
        ];

        if (!$this->validate($aturanValidasi)) {
            session()->setFlashdata('error', $this->validator->getError('foto_sesudah'));
            return redirect()->to('/caraka/kerjakan/' . $tiket_id)->withInput();
        }

        $foto = $this->request->getFile('foto_sesudah');

        // 2. MULAI TRANSAKSI DATABASE
        $db->transStart();

        // Update status tiket & tanggal selesai
        $tiketModel->update($tiket_id, [
            'status'      => 'menunggu_approval',
            'tgl_selesai' => date('Y-m-d H:i:s')
        ]);

        // 3. SIAPKAN FOLDER SEMENTARA (TEMP)
        $newName  = $foto->getRandomName();
        $tempPath = FCPATH . 'uploads/temp/';

        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        $foto->move($tempPath, $newName);
        $fullPath = $tempPath . $newName;
        $mimeType = mime_content_type($fullPath);

        // 4. UPLOAD KE GOOGLE DRIVE
        try {
            $driveService   = new \App\Libraries\GoogleDriveService();
            $google_file_id = $driveService->uploadImage($fullPath, $newName, $mimeType);

            // Hapus file sementara di lokal setelah berhasil diunggah ke Drive
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Simpan metadata ke tabel tiket_foto
            $db->table('tiket_foto')->insert([
                'tiket_id'       => $tiket_id,
                'tipe_foto'      => 'sesudah',
                'google_file_id' => $google_file_id,
                'diupload_oleh'  => session()->get('id'),
                'tgl_upload'     => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            session()->setFlashdata('error', 'Gagal mengunggah foto ke Google Drive: ' . $e->getMessage());
            return redirect()->to('/caraka/kerjakan/' . $tiket_id)->withInput();
        }

        $db->transComplete();

        // 5. CEK STATUS AKHIR TRANSAKSI
        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'Terjadi kesalahan sistem database saat menyimpan laporan.');
            return redirect()->to('/caraka/kerjakan/' . $tiket_id)->withInput();
        }

        session()->setFlashdata('success', 'Tugas berhasil diselesaikan dan tersimpan di Cloud. Menunggu persetujuan Admin.');
        return redirect()->to('/caraka/dashboard');
    }

    public function riwayat()
    {
        $tiketModel = new TiketModel();
        $caraka_id  = session()->get('id');

        // Tangkap parameter filter (default bulan/tahun ini)
        $bulan_aktif = $this->request->getGet('bulan') ?? date('m');
        $tahun_aktif = $this->request->getGet('tahun') ?? date('Y');

        $data = [
            'title'       => 'Riwayat Pekerjaan',
            'bulan_aktif' => $bulan_aktif,
            'tahun_aktif' => $tahun_aktif,
            'tugas'       => $tiketModel->select('tiket_perawatan.*')
                                  ->where("FIND_IN_SET('$caraka_id', caraka_id) > 0")
                                  ->where('status', 'selesai') 
                                  // Filter berdasarkan tgl_selesai
                                  ->where('MONTH(tgl_selesai)', $bulan_aktif)
                                  ->where('YEAR(tgl_selesai)', $tahun_aktif)
                                  ->orderBy('tgl_selesai', 'DESC') 
                                  ->findAll()
        ];
        
        return view('caraka/riwayat', $data);
    }

    public function detail($tiket_id)
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();
        $caraka_id  = session()->get('id');

        // Ambil tiket, join ke user untuk tahu siapa pelapornya
        $tiket = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                            ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                            ->where('tiket_perawatan.id', $tiket_id)
                            ->first();

        if (empty($tiket)) {
            return redirect()->to('/caraka/riwayat');
        }

        // Pastikan tiket ini memang tugas dari caraka yang login
        $allowed_carakas = explode(',', $tiket['caraka_id']);
        if(!in_array($caraka_id, $allowed_carakas)) {
            return redirect()->to('/caraka/riwayat');
        }

        // Ambil foto
        $foto_sebelum = $db->table('tiket_foto')->where('tiket_id', $tiket_id)->where('tipe_foto', 'sebelum')->orderBy('id', 'DESC')->get()->getRowArray();
        $foto_sesudah = $db->table('tiket_foto')->where('tiket_id', $tiket_id)->where('tipe_foto', 'sesudah')->orderBy('id', 'DESC')->get()->getRowArray();

        $data = [
            'title'        => 'Detail Riwayat',
            'tiket'        => $tiket,
            'foto_sebelum' => $foto_sebelum,
            'foto_sesudah' => $foto_sesudah
        ];

        return view('caraka/detail_riwayat', $data);
    }

    // app/Controllers/CarakaController.php
    
    public function renderFoto($fileId)
    {
        try {
            $driveService = new \App\Libraries\GoogleDriveService();
            $gambarData = $driveService->getMedia($fileId);
            
            return $this->response
                        ->setContentType('image/jpeg') 
                        ->setHeader('Cache-Control', 'max-age=86400')
                        ->setBody($gambarData);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(404)->setBody('Gambar tidak ditemukan');
        }
    }
}