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
        $status    = $this->request->getPost('status'); // Menangkap status dari dropdown modal assign
        $prioritas = $this->request->getPost('prioritas');
        $tgl_tugas = $this->request->getPost('tgl_dijadwalkan'); 
        $catatan   = $this->request->getPost('catatan_admin'); // Menangkap alasan ditolak/pending
        
        // Data dasar yang akan diupdate
        $dataUpdate = [
            'status' => $status
        ];

        // Jika status BUKAN ditolak atau pending, berarti murni penugasan (dikerjakan)
        if ($status !== 'ditolak' && $status !== 'pending') {
            $caraka_array = $this->request->getPost('caraka_id'); 
            $caraka_string = !empty($caraka_array) ? implode(',', (array)$caraka_array) : null; 
            
            $dataUpdate['caraka_id']       = $caraka_string;
            $dataUpdate['prioritas']       = $prioritas;
            $dataUpdate['tgl_dijadwalkan'] = $tgl_tugas;
        }

        // Jika admin mengisi catatan alasan penolakan/penundaan
        if (!empty($catatan)) {
            $dataUpdate['catatan_admin'] = $catatan;
        }

        // Eksekusi update ke database
        $tiketModel->update($tiket_id, $dataUpdate);

        // Atur pesan sukses dinamis berdasarkan status
        if ($status == 'ditolak') {
            session()->setFlashdata('success', 'Laporan berhasil ditolak dan dikembalikan ke pelapor.');
        } elseif ($status == 'pending') {
            session()->setFlashdata('success', 'Laporan diubah menjadi status Pending.');
        } else {
            session()->setFlashdata('success', 'Tiket berhasil ditugaskan ke Caraka.');
        }

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

    public function laporan()
    {
        $tiketModel = new TiketModel();
        $userModel  = new UserModel();
        
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $status = $this->request->getGet('status') ?? '';

        $caraka = $userModel->where('role', 'caraka')->where('is_active', 1)->findAll();

        $builder = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                              ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                              ->where('MONTH(tiket_perawatan.tgl_dibuat)', $bulan)
                              ->where('YEAR(tiket_perawatan.tgl_dibuat)', $tahun);

        if (!empty($status)) {
            $builder->where('tiket_perawatan.status', $status);
        }

        $tiket_raw = $builder->orderBy('tiket_perawatan.tgl_dibuat', 'DESC')->findAll();

        $tiket_lengkap = [];
        foreach ($tiket_raw as $t) {
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

        $data = [
            'title'  => 'Rekap & Cetak Laporan',
            'tiket'  => $tiket_lengkap,
            'bulan'  => $bulan,
            'tahun'  => $tahun,
            'status' => $status
        ];

        return view('admin/laporan', $data); // Diarahkan ke view admin
    }

    public function cetak()
    {
        $tiketModel = new TiketModel();
        $userModel  = new UserModel();
        
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $status = $this->request->getGet('status') ?? '';

        $caraka = $userModel->where('role', 'caraka')->where('is_active', 1)->findAll();

        $builder = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                              ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                              ->where('MONTH(tiket_perawatan.tgl_dibuat)', $bulan)
                              ->where('YEAR(tiket_perawatan.tgl_dibuat)', $tahun);

        if (!empty($status)) {
            $builder->where('tiket_perawatan.status', $status);
        }

        $tiket_raw = $builder->orderBy('tiket_perawatan.tgl_dibuat', 'ASC')->findAll();

        $tiket_lengkap = [];
        foreach ($tiket_raw as $t) {
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

        $data = [
            'title'  => 'Cetak Laporan',
            'tiket'  => $tiket_lengkap,
            'bulan'  => $bulan,
            'tahun'  => $tahun,
            'status' => $status
        ];

        return view('admin/cetak_laporan', $data); // Diarahkan ke view admin
    }

    // ==========================================
    // BAGIAN KELOLA USER
    // ==========================================
    public function users()
    {
        $userModel = new UserModel();
        
        $data = [
            'title' => 'Kelola Pengguna',
            'users' => $userModel->orderBy('role', 'ASC')->orderBy('nama_lengkap', 'ASC')->findAll()
        ];

        return view('admin/kelola_user', $data);
    }

    public function tambahUser()
    {
        $userModel = new UserModel();
        
        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'username'     => $this->request->getPost('username'),
            'password'     => password_hash((string)$this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'         => $this->request->getPost('role'),
            'is_active'    => 1 // Aktif secara default
        ];

        $userModel->insert($data);
        session()->setFlashdata('success', 'Pengguna baru berhasil ditambahkan.');
        return redirect()->to('/admin/user');
    }

    public function editUser()
    {
        $userModel = new UserModel();
        $id = $this->request->getPost('id');
        
        $data = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'username'     => $this->request->getPost('username'),
            'role'         => $this->request->getPost('role'),
            'is_active'    => $this->request->getPost('is_active')
        ];

        // Jika form password diisi, berarti Admin mereset passwordnya
        $password_baru = $this->request->getPost('password');
        if (!empty($password_baru)) {
            $data['password'] = password_hash((string)$password_baru, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);
        session()->setFlashdata('success', 'Data pengguna berhasil diperbarui.');
        return redirect()->to('/admin/user');
    }

    public function hapusUser($id)
    {
        $userModel = new UserModel();
        
        // Proteksi: Mencegah Admin menghapus dirinya sendiri
        if ($id == session()->get('id')) {
            session()->setFlashdata('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
            return redirect()->to('/admin/user');
        }

        try {
            // Mencoba menghapus user
            $userModel->delete($id);
            session()->setFlashdata('success', 'Akun pengguna berhasil dihapus.');
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            // Menangkap error jika user terikat dengan foreign key constraint (Error 1451)
            if (strpos($e->getMessage(), 'foreign key constraint') !== false || strpos($e->getMessage(), '1451') !== false) {
                session()->setFlashdata('error', 'Gagal! Pengguna ini tidak dapat dihapus karena memiliki riwayat laporan atau data foto. Silakan edit dan ubah statusnya menjadi "Tidak Aktif".');
            } else {
                // Menangkap error database lainnya
                session()->setFlashdata('error', 'Terjadi kesalahan database: ' . $e->getMessage());
            }
        }

        return redirect()->to('/admin/user');
    }

    // ==========================================
    // BAGIAN LAPORAN SAYA (ADMIN SEBAGAI PELAPOR)
    // ==========================================
    public function laporanSaya()
    {
        $tiketModel = new TiketModel();
        $admin_id = session()->get('id');

        // Ambil filter dari URL, default bulan dan tahun saat ini
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');

        // Mengambil tiket yang dibuat admin berdasarkan filter bulan dan tahun
        $data = [
            'title' => 'Laporan Saya',
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tiket' => $tiketModel->where('pelapor_id', $admin_id)
                                  ->where('MONTH(tgl_dibuat)', $bulan)
                                  ->where('YEAR(tgl_dibuat)', $tahun)
                                  ->orderBy('tgl_dibuat', 'DESC')
                                  ->findAll()
        ];

        return view('admin/laporan_saya', $data);
    }

    public function simpanLaporanSaya()
    {
        $tiketModel = new TiketModel();
        $db = \Config\Database::connect();
        
        $admin_id = session()->get('id');

        // 1. Cek validasi wajib foto terlebih dahulu
        $foto = $this->request->getFile('foto');
        if (!$foto || !$foto->isValid() || $foto->hasMoved()) {
            session()->setFlashdata('error', 'Foto kondisi awal wajib diunggah!');
            return redirect()->to('/admin/laporan_saya');
        }

        $db->transStart();

        // 2. Simpan Data Utama Tiket
        $dataTiket = [
            'jenis_tiket'   => 'insidental',
            'judul_laporan' => $this->request->getPost('judul_laporan'),
            'lokasi_manual' => $this->request->getPost('lokasi_manual'),
            'deskripsi'     => $this->request->getPost('deskripsi'),
            'pelapor_id'    => $admin_id,
            'status'        => 'menunggu_respon',
            'prioritas'     => 'sedang',
            'tgl_dibuat'    => date('Y-m-d H:i:s')
        ];
        
        $tiketModel->insert($dataTiket);
        $tiket_id = $tiketModel->getInsertID();

        // 3. PROSES UPLOAD KE GOOGLE DRIVE
        // a. Pindahkan foto ke folder sementara (temp)
        $newName = 'ADMIN_' . $tiket_id . '_' . $foto->getRandomName();
        $tempPath = FCPATH . 'uploads/temp/'; 
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
                'google_file_id' => $google_file_id, 
                'diupload_oleh'  => $admin_id,
                'tgl_upload'     => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            $db->transRollback(); // Batalkan penyimpanan tiket
            
            // Hapus file temp jika tertinggal
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            session()->setFlashdata('error', 'Gagal mengunggah foto ke Google Drive: ' . $e->getMessage());
            return redirect()->to('/admin/laporan_saya')->withInput();
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            session()->setFlashdata('error', 'Gagal mengirim laporan. Terjadi kesalahan pada database.');
        } else {
            session()->setFlashdata('success', 'Laporan berhasil dikirim dan tersimpan di Google Drive.');
        }
        
        return redirect()->to('/admin/laporan_saya');
    }

    public function renderFoto($fileId)
    {
        try {
            $driveService = new \App\Libraries\GoogleDriveService();
            // Ambil data mentah dari Google Drive
            $gambarData = $driveService->getMedia($fileId);
            
            // Perintahkan CodeIgniter untuk merender ini sebagai gambar utuh
            return $this->response
                        ->setContentType('image/jpeg') 
                        ->setHeader('Cache-Control', 'max-age=86400') // Cache 1 hari agar server tidak berat
                        ->setBody($gambarData);
                        
        } catch (\Exception $e) {
            // Jika ID salah atau gagal, berikan respon error
            return $this->response->setStatusCode(404)->setBody('Gambar tidak ditemukan');
        }
    }
}