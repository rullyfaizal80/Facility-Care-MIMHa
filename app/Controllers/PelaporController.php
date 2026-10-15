<?php
namespace App\Controllers;

use App\Models\TiketModel;

class PelaporController extends BaseController
{
    public function index()
    {
        $tiketModel = new TiketModel();
        $pelapor_id = session()->get('id');

        // Tangkap parameter GET untuk filter, default adalah bulan dan tahun saat ini
        $bulan_aktif = $this->request->getGet('bulan') ?? date('m');
        $tahun_aktif = $this->request->getGet('tahun') ?? date('Y');
        
        $data = [
            'title'       => 'Laporan Saya',
            'bulan_aktif' => $bulan_aktif,
            'tahun_aktif' => $tahun_aktif,
            // Filter berdasarkan pelapor dan bulan/tahun yang dipilih
            'tiket'       => $tiketModel->select('tiket_perawatan.*')
                                        ->where('pelapor_id', $pelapor_id)
                                        ->where('MONTH(tgl_dibuat)', $bulan_aktif)
                                        ->where('YEAR(tgl_dibuat)', $tahun_aktif)
                                        ->orderBy('tgl_dibuat', 'DESC')
                                        ->findAll()
        ];
        
        return view('pelapor/dashboard', $data);
    }

    public function detail($tiket_id)
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();

        // Ambil data tiket, pastikan hanya milik pelapor yang sedang login
        $tiket = $tiketModel->select('tiket_perawatan.*, caraka.nama_lengkap as nama_caraka')
                            ->join('users as caraka', 'caraka.id = tiket_perawatan.caraka_id', 'left')
                            ->where('tiket_perawatan.id', $tiket_id)
                            ->where('tiket_perawatan.pelapor_id', session()->get('id'))
                            ->first();

        if (empty($tiket)) {
            session()->setFlashdata('error', 'Laporan tidak ditemukan.');
            return redirect()->to('/pelapor/dashboard');
        }

        // Ambil foto
        $foto_sebelum = $db->table('tiket_foto')->where('tiket_id', $tiket_id)->where('tipe_foto', 'sebelum')->orderBy('id', 'DESC')->get()->getRowArray();
        $foto_sesudah = $db->table('tiket_foto')->where('tiket_id', $tiket_id)->where('tipe_foto', 'sesudah')->orderBy('id', 'DESC')->get()->getRowArray();

        $data = [
            'title'        => 'Detail Laporan',
            'tiket'        => $tiket,
            'foto_sebelum' => $foto_sebelum,
            'foto_sesudah' => $foto_sesudah
        ];

        return view('pelapor/detail_laporan', $data);
    }

    // ... fungsi buat() dan simpan() tetap sama seperti sebelumnya ...
    public function buat()
    {
        $data = ['title' => 'Buat Laporan Baru'];
        return view('pelapor/buat_laporan', $data);
    }

    public function simpan()
    {
        $tiketModel = new \App\Models\TiketModel();
        $db = \Config\Database::connect();

        // 1. Cek validasi wajib foto terlebih dahulu
        $foto = $this->request->getFile('foto_kondisi');
        if (!$foto || !$foto->isValid() || $foto->hasMoved()) {
            session()->setFlashdata('error', 'Foto kondisi kerusakan wajib diunggah!');
            return redirect()->back()->withInput();
        }

        $db->transStart();

        // 2. Simpan Data Tiket ke Database (Sama seperti sebelumnya)
        $dataTiket = [
            'jenis_tiket'   => 'insidental',
            'judul_laporan' => $this->request->getPost('judul_laporan'),
            'lokasi_manual' => $this->request->getPost('lokasi_manual'), 
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'pelapor_id'    => session()->get('id'),
            'status'        => 'menunggu_respon',
            'prioritas'     => 'sedang',
            'tgl_dibuat'    => date('Y-m-d H:i:s')
        ];
        
        $tiketModel->insert($dataTiket);
        $tiket_id = $tiketModel->getInsertID();

        // 3. PROSES UPLOAD KE GOOGLE DRIVE
        // a. Pindahkan foto ke folder sementara (temp)
        $newName = $foto->getRandomName();
        $tempPath = FCPATH . 'uploads/temp/'; // Path absolut ke public/uploads/temp
        $foto->move($tempPath, $newName);
        
        $fullPath = $tempPath . $newName;
        $mimeType = mime_content_type($fullPath);

        try {
            // b. Panggil Library Google Drive dan eksekusi Upload
            $driveService = new \App\Libraries\GoogleDriveService();
            $google_file_id = $driveService->uploadImage($fullPath, $newName, $mimeType);

            // c. Hapus file sementara di lokal server agar server tidak penuh
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // 4. Simpan Referensi ID Google Drive ke Database
            $db->table('tiket_foto')->insert([
                'tiket_id'       => $tiket_id,
                'tipe_foto'      => 'sebelum',
                // Kita hilangkan prefix LOKAL_ karena sekarang sudah dari GDrive
                'google_file_id' => $google_file_id, 
                'diupload_oleh'  => session()->get('id'),
                'tgl_upload'     => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            // Jika upload ke GDrive gagal (misal koneksi terputus atau API limit)
            $db->transRollback(); // Batalkan penyimpanan tiket
            
            // Hapus file temp jika tertinggal
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            session()->setFlashdata('error', 'Gagal mengunggah foto ke Cloud: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'Gagal mengirim laporan. Terjadi kesalahan pada database.');
        } else {
            session()->setFlashdata('success', 'Laporan berhasil dikirim dan tersimpan di Cloud.');
        }
        return redirect()->to('/pelapor/dashboard');
    }

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