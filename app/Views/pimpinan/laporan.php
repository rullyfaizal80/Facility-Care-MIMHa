<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item">
        <a class="nav-link" href="/pimpinan/dashboard">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard Eksekutif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="/pimpinan/laporan">
            <i class="fas fa-file-alt me-2"></i> Rekap & Cetak
        </a>
    </li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Rekapitulasi & Cetak Laporan</h1>
    </div>

    <!-- FILTER FORM -->
    <form method="get" action="/pimpinan/laporan" class="bg-light p-3 rounded border mb-4 shadow-sm row g-3 align-items-center">
        <div class="col-md-3">
            <label class="form-label small fw-bold">Bulan</label>
            <select name="bulan" class="form-select form-select-sm">
                <?php 
                $arrBulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                foreach($arrBulan as $k => $v): 
                    $sel = ($k == $bulan) ? 'selected' : '';
                ?>
                    <option value="<?= $k ?>" <?= $sel ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold">Tahun</label>
            <select name="tahun" class="form-select form-select-sm">
                <?php 
                $thn_sekarang = date('Y');
                for($t = $thn_sekarang - 2; $t <= $thn_sekarang; $t++): 
                    $sel = ($t == $tahun) ? 'selected' : '';
                ?>
                    <option value="<?= $t ?>" <?= $sel ?>><?= $t ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="menunggu_respon" <?= $status == 'menunggu_respon' ? 'selected' : '' ?>>Baru (Menunggu Respon)</option>
                <option value="dikerjakan" <?= $status == 'dikerjakan' ? 'selected' : '' ?>>Dikerjakan</option>
                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="menunggu_approval" <?= $status == 'menunggu_approval' ? 'selected' : '' ?>>Menunggu Approval</option>
                <option value="selesai" <?= $status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                <option value="ditolak" <?= $status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2 mt-4">
            <button type="submit" class="btn btn-sm btn-primary px-3"><i class="fas fa-filter"></i> Filter</button>
            <a href="/pimpinan/laporan/cetak?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>&status=<?= $status ?>" target="_blank" class="btn btn-sm btn-success px-3">
                <i class="fas fa-print"></i> Cetak / PDF
            </a>
        </div>
    </form>

    <!-- TABEL DATA LAPORAN -->
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <!-- TABEL DATA LAPORAN -->
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Pelapor</th>
                    <th>Lokasi</th>
                    <th>Masalah</th>
                    <th>Status</th>
                    <th>Teknisi</th>
                    <!-- TAMBAHAN: Kolom Aksi -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($tiket)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada data laporan pada periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($tiket as $t): ?>
                        <tr>
                            <!-- ... (Kolom Tanggal sampai Teknisi biarkan seperti sebelumnya) ... -->
                            <td>
                                <div class="small" style="white-space: nowrap;">
                                    <?= date('d M Y, H:i', strtotime($t['tgl_dibuat'])) ?>
                                </div>
                            </td>
                            <td><?= esc($t['nama_pelapor'] ?? 'Sistem') ?></td>
                            <td><strong><?= esc($t['lokasi_manual']) ?></strong></td>
                            <td><?= esc($t['judul_laporan']) ?></td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($t['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                                    if($t['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                                    if($t['status'] == 'pending') $badge = 'bg-danger'; 
                                    if($t['status'] == 'menunggu_approval') $badge = 'bg-primary';
                                    if($t['status'] == 'selesai') $badge = 'bg-success';
                                    if($t['status'] == 'ditolak') $badge = 'bg-dark'; 
                                ?>
                                <span class="badge <?= $badge ?> rounded-pill"><?= str_replace('_', ' ', strtoupper($t['status'])) ?></span>
                            </td>
                            <td><small class="text-muted fw-bold"><?= esc($t['nama_caraka']) ?></small></td>
                            
                            <!-- TAMBAHAN: Tombol Detail -->
                            <td>
                                <a href="/pimpinan/tiket/detail/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
<?= $this->endSection() ?>