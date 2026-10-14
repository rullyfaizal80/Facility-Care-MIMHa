<?php
namespace App\Controllers;

use App\Models\TiketModel;
use App\Models\UserModel;

class PimpinanController extends BaseController
{
    public function index()
    {
        $tiketModel = new TiketModel();
        
        $bulan_ini = date('m');
        $tahun_ini = date('Y');

        // 1. STATISTIK CARD (BULAN INI)
        $data = [
            'title'          => 'Dashboard Pimpinan',
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

        return view('pimpinan/dashboard', $data);
    }

    public function laporan()
    {
        $tiketModel = new TiketModel();
        $userModel  = new UserModel();
        
        // Ambil filter bulan & tahun, default ke bulan/tahun saat ini
        $bulan = $this->request->getGet('bulan') ?? date('m');
        $tahun = $this->request->getGet('tahun') ?? date('Y');
        $status = $this->request->getGet('status') ?? '';

        $caraka = $userModel->where('role', 'caraka')->where('is_active', 1)->findAll();

        // Query berdasarkan filter
        $builder = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                              ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                              ->where('MONTH(tiket_perawatan.tgl_dibuat)', $bulan)
                              ->where('YEAR(tiket_perawatan.tgl_dibuat)', $tahun);

        if (!empty($status)) {
            $builder->where('tiket_perawatan.status', $status);
        }

        $tiket_raw = $builder->orderBy('tiket_perawatan.tgl_dibuat', 'DESC')->findAll();

        // Format nama multi-caraka
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

        return view('pimpinan/laporan', $data);
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

        return view('pimpinan/cetak_laporan', $data);
    }

    public function detail($tiket_id)
    {
        $tiketModel = new \App\Models\TiketModel();
        $userModel  = new \App\Models\UserModel();
        $db = \Config\Database::connect();

        // 1. Ambil data utama tiket
        $tiket = $tiketModel->select('tiket_perawatan.*, users.nama_lengkap as nama_pelapor')
                            ->join('users', 'users.id = tiket_perawatan.pelapor_id', 'left')
                            ->where('tiket_perawatan.id', $tiket_id)
                            ->first();

        // Jika tidak ditemukan, kembalikan ke daftar laporan
        if (empty($tiket)) {
            return redirect()->to('/pimpinan/laporan');
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
            'title'        => 'Detail Laporan Perawatan',
            'tiket'        => $tiket,
            'foto_sebelum' => $foto_sebelum,
            'foto_sesudah' => $foto_sesudah
        ];

        return view('pimpinan/detail_tiket', $data);
    }
    
}