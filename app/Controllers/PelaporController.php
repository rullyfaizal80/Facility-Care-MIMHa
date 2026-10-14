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
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();

        // 1. Cek validasi wajib foto terlebih dahulu sebelum transaksi berjalan
        $foto = $this->request->getFile('foto_kondisi');
        if (!$foto || !$foto->isValid() || $foto->hasMoved()) {
            session()->setFlashdata('error', 'Foto kondisi kerusakan wajib diunggah!');
            return redirect()->back()->withInput();
        }

        $db->transStart();

        $dataTiket = [
            'jenis_tiket'   => 'insidental',
            'judul_laporan' => $this->request->getPost('judul_laporan'),
            'lokasi_manual' => $this->request->getPost('lokasi_manual'), 
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'pelapor_id'    => session()->get('id'),
            'status'        => 'menunggu_respon',
            'prioritas'     => 'sedang', // Ditambahkan agar selaras dengan kolom prioritas jika ada
            'tgl_dibuat'    => date('Y-m-d H:i:s')
        ];
        
        $tiketModel->insert($dataTiket);
        $tiket_id = $tiketModel->getInsertID();

        // 2. Proses pemindahan file foto yang sudah dipastikan valid
        $newName = $foto->getRandomName();
        $foto->move('uploads/laporan', $newName);
        
        $db->table('tiket_foto')->insert([
            'tiket_id'       => $tiket_id,
            'tipe_foto'      => 'sebelum',
            'google_file_id' => 'LOKAL_'.$newName, 
            'diupload_oleh'  => session()->get('id'),
            'tgl_upload'     => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'Gagal mengirim laporan. Terjadi kesalahan pada database.');
        } else {
            session()->setFlashdata('success', 'Laporan berhasil dikirim dan sedang menunggu respon Admin.');
        }
        return redirect()->to('/pelapor/dashboard');
    }
}