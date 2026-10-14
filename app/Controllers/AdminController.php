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
        
        $bulan_ini = date('m');
        $tahun_ini = date('Y');

        // 1. STATISTIK CARD (HANYA BULAN INI)
        $data = [
            'title'          => 'Dashboard Admin',
            'bulan_ini'      => $bulan_ini,
            'tahun_ini'      => $tahun_ini,
            'tiket_masuk'    => $tiketModel->where('status', 'menunggu_respon')->where('MONTH(tgl_dibuat)', $bulan_ini)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults(),
            'tiket_proses'   => $tiketModel->whereIn('status', ['dikerjakan', 'pending'])->where('MONTH(tgl_dibuat)', $bulan_ini)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults(),
            'tiket_approval' => $tiketModel->where('status', 'menunggu_approval')->where('MONTH(tgl_dibuat)', $bulan_ini)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults(),
            'tiket_selesai'  => $tiketModel->where('status', 'selesai')->where('MONTH(tgl_dibuat)', $bulan_ini)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults(),
            'tiket_ditolak'  => $tiketModel->where('status', 'ditolak')->where('MONTH(tgl_dibuat)', $bulan_ini)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults(),
        ];

        // 2. REKAPITULASI TABEL (1 TAHUN INI)
        $rekap = [];
        $total_tahun = ['masuk' => 0, 'proses' => 0, 'approval' => 0, 'selesai' => 0, 'ditolak' => 0, 'total' => 0];

        for ($i = 1; $i <= 12; $i++) {
            $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
            $masuk    = $tiketModel->where('status', 'menunggu_respon')->where('MONTH(tgl_dibuat)', $bln)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults();
            $proses   = $tiketModel->whereIn('status', ['dikerjakan', 'pending'])->where('MONTH(tgl_dibuat)', $bln)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults();
            $approval = $tiketModel->where('status', 'menunggu_approval')->where('MONTH(tgl_dibuat)', $bln)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults();
            $selesai  = $tiketModel->where('status', 'selesai')->where('MONTH(tgl_dibuat)', $bln)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults();
            $ditolak  = $tiketModel->where('status', 'ditolak')->where('MONTH(tgl_dibuat)', $bln)->where('YEAR(tgl_dibuat)', $tahun_ini)->countAllResults();
            
            $total_bulan = $masuk + $proses + $approval + $selesai + $ditolak;

            $rekap[] = [
                'bulan' => $bln, 'masuk' => $masuk, 'proses' => $proses, 'approval' => $approval, 'selesai' => $selesai, 'ditolak' => $ditolak, 'total' => $total_bulan
            ];

            $total_tahun['masuk'] += $masuk; $total_tahun['proses'] += $proses; $total_tahun['approval'] += $approval;
            $total_tahun['selesai'] += $selesai; $total_tahun['ditolak'] += $ditolak; $total_tahun['total'] += $total_bulan;
        }

        $data['rekap'] = $rekap;
        $data['total_tahun'] = $total_tahun;

        return view('admin/dashboard', $data);
    }

    public function tiket()
    {
        $tiketModel = new TiketModel();
        $userModel  = new UserModel();
        $db = \Config\Database::connect();
        
        // 1. Ambil filter dari URL, jika kosong gunakan bulan dan tahun saat ini
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        $caraka = $userModel->where('role', 'caraka')->where('is_active', 1)->findAll();

        // 2. Query berdasarkan filter bulan dan tahun
        $tiket_raw = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                            ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                            ->where('MONTH(tiket_perawatan.tgl_dibuat)', $bulan)
                            ->where('YEAR(tiket_perawatan.tgl_dibuat)', $tahun)
                            ->orderBy('tiket_perawatan.tgl_dibuat', 'DESC')
                            ->findAll();

        // 3. Proses nama multi-caraka & Ambil Foto
        $tiket_lengkap = [];
        foreach ($tiket_raw as $t) {
            // Ambil foto sebelum
            $foto = $db->table('tiket_foto')->where('tiket_id', $t['id'])->where('tipe_foto', 'sebelum')->orderBy('id', 'DESC')->get()->getRowArray();
            $t['foto_sebelum'] = $foto ? $foto['google_file_id'] : null;

            // Proses Nama Caraka
            $t['nama_caraka'] = '-';
            if (!empty($t['caraka_id'])) {
                $caraka_ids = explode(',', $t['caraka_id']);
                $nama_nama = [];
                foreach ($caraka as $c) {
                    if (in_array($c['id'], $caraka_ids)) {
                        $nama_nama[] = $c['nama_lengkap'];
                    }
                }
                $t['nama_caraka'] = implode(', ', $nama_nama);
            }
            $tiket_lengkap[] = $t;
        }

        // 4. Masukkan ke data
        $data = [
            'title'  => 'Kelola Tiket',
            'tiket'  => $tiket_lengkap,
            'caraka' => $caraka,
            'bulan'  => $bulan,
            'tahun'  => $tahun
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
        $userModel  = new UserModel();
        $db = \Config\Database::connect();

        // 1. Ambil data utama tiket (tanpa join fasilitas, cukup join pelapor)
        $tiket = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                            ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                            ->where('tiket_perawatan.id', $tiket_id)
                            ->first();

        // Jika tidak ditemukan, kembalikan ke kelola tiket
        if (empty($tiket)) {
            return redirect()->to('/admin/tiket');
        }

        // 2. Ambil Nama-nama Caraka (Logika Multi-Caraka)
        $tiket['nama_caraka'] = '-';
        if (!empty($tiket['caraka_id'])) {
            $caraka_ids = explode(',', $tiket['caraka_id']); 
            $caraka_users = $userModel->whereIn('id', $caraka_ids)->findAll();
            $nama_nama = [];
            foreach ($caraka_users as $cu) {
                $nama_nama[] = $cu['nama_lengkap'];
            }
            $tiket['nama_caraka'] = implode(', ', $nama_nama);
        }

        // 3. Ambil data foto (Sebelum & Sesudah)
        $foto_sebelum = $db->table('tiket_foto')->where('tiket_id', $tiket_id)->where('tipe_foto', 'sebelum')->orderBy('id', 'DESC')->get()->getRowArray();
        $foto_sesudah = $db->table('tiket_foto')->where('tiket_id', $tiket_id)->where('tipe_foto', 'sesudah')->orderBy('id', 'DESC')->get()->getRowArray();

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

    public function updateTiket()
    {
        $tiketModel = new TiketModel();
        
        $tiket_id  = $this->request->getPost('tiket_id');
        $status    = $this->request->getPost('status');
        $prioritas = $this->request->getPost('prioritas');
        $tgl_tugas = $this->request->getPost('tgl_dijadwalkan');
        $catatan   = $this->request->getPost('catatan_admin'); // Menangkap inputan catatan
        
        // Ambil array caraka yang diceklis
        $caraka_array = $this->request->getPost('caraka_id'); 
        $caraka_string = !empty($caraka_array) ? implode(',', (array)$caraka_array) : null; 

        // Siapkan data yang akan di-update
        $dataUpdate = [
            'caraka_id'       => $caraka_string,
            'prioritas'       => $prioritas,
            'tgl_dijadwalkan' => $tgl_tugas,
            'status'          => $status
        ];

        // Jika admin mengisi catatan (misal alasan ditolak/pending), masukkan ke data
        if ($catatan !== null) {
            $dataUpdate['catatan_admin'] = $catatan;
        }

        // Jika Admin secara manual mengubah status menjadi 'selesai', pastikan tanggal selesainya tercatat
        if ($status == 'selesai') {
            $dataUpdate['tgl_selesai'] = date('Y-m-d H:i:s');
        }

        // Eksekusi update ke database
        $tiketModel->update($tiket_id, $dataUpdate);

        session()->setFlashdata('success', 'Perubahan tiket berhasil disimpan.');
        return redirect()->to('/admin/tiket');
    }
}