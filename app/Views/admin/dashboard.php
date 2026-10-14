<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item">
        <a class="nav-link active" href="/admin/dashboard">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="/admin/tiket">
            <i class="fas fa-ticket-alt me-2"></i> Kelola Tiket
        </a>
    </li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard Admin</h1>
    </div>

    <!-- JUDUL STATISTIK BULAN INI -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
        <h5 class="fw-bold mb-0">
            <i class="fas fa-chart-pie text-primary me-2"></i> Statistik Bulan Ini
        </h5>
        <?php
            $namaBulanList = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $namaBulanIni = $namaBulanList[(int)$bulan_ini];
        ?>
        <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill shadow-sm">
            <i class="fas fa-calendar-day me-1"></i> <?= $namaBulanIni ?> <?= $tahun_ini ?>
        </span>
    </div>

    <!-- Diubah dari 3 kolom (col-md-4) menjadi 4 kolom (col-md-3) -->
    <div class="row">
        
        <!-- Card 1: Menunggu Respon -->
        <div class="col-md-4 col-xl mb-3">
            <div class="card text-dark bg-warning h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-title fw-bold"><i class="fas fa-exclamation-circle"></i> Laporan Baru</h6>
                    <p class="card-text display-5 fw-bold mb-0"><?= $tiket_masuk ?></p>
                    <small>Menunggu Ditugaskan</small>
                </div>
            </div>
        </div>
        
        <!-- Card 2: Sedang Dikerjakan & Pending -->
        <div class="col-md-4 col-xl mb-3">
            <div class="card text-dark bg-info h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-title fw-bold"><i class="fas fa-tools"></i> Sedang Proses</h6>
                    <p class="card-text display-5 fw-bold mb-0"><?= $tiket_proses ?></p>
                    <small>Dikerjakan & Pending</small>
                </div>
            </div>
        </div>

        <!-- Card 3: Menunggu Approval -->
        <div class="col-md-4 col-xl mb-3">
            <div class="card text-white bg-primary h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-title fw-bold"><i class="fas fa-search"></i> Butuh Review</h6>
                    <p class="card-text display-5 fw-bold mb-0"><?= $tiket_approval ?></p>
                    <small>Menunggu Approval</small>
                </div>
            </div>
        </div>
        
        <!-- Card 4: Selesai -->
        <div class="col-md-6 col-xl mb-3">
            <div class="card text-white bg-success h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-title fw-bold"><i class="fas fa-check-double"></i> Total Selesai</h6>
                    <p class="card-text display-5 fw-bold mb-0"><?= $tiket_selesai ?></p>
                    <small>Laporan Tuntas</small>
                </div>
            </div>
        </div>

        <!-- Card 5: Ditolak (BARU) -->
        <div class="col-md-6 col-xl mb-3">
            <div class="card text-white bg-dark h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="card-title fw-bold"><i class="fas fa-times-circle"></i> Ditolak</h6>
                    <p class="card-text display-5 fw-bold mb-0"><?= $tiket_ditolak ?></p>
                    <small>Laporan Dibatalkan</small>
                </div>
            </div>
        </div>
        
    </div>

    <hr class="my-4 border-2 border-secondary opacity-25">
    <!-- TABEL REKAPITULASI 1 TAHUN -->
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-alt text-primary me-1"></i> Rekapitulasi Laporan Tahun <?= $tahun_ini ?></span>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered mb-0 text-center align-middle">
            <thead class="table-light text-muted small">
                <tr>
                    <th>Bulan</th>
                    <th><i class="fas fa-exclamation-circle text-warning"></i> Baru</th>
                    <th><i class="fas fa-tools text-info"></i> Proses</th>
                    <th><i class="fas fa-search text-primary"></i> Review</th>
                    <th><i class="fas fa-check-double text-success"></i> Selesai</th>
                    <th><i class="fas fa-times-circle text-dark"></i> Ditolak</th>
                    <th class="bg-light fw-bold">Total Laporan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                foreach($rekap as $r): 
                    // Highlight bulan ini
                    $isBulanIni = ($r['bulan'] == $bulan_ini) ? 'table-primary bg-opacity-25 fw-bold' : '';
                ?>
                <tr class="<?= $isBulanIni ?>">
                    <td class="text-start ps-3"><?= $namaBulan[(int)$r['bulan']] ?></td>
                    <td><?= $r['masuk'] ?: '-' ?></td>
                    <td><?= $r['proses'] ?: '-' ?></td>
                    <td><?= $r['approval'] ?: '-' ?></td>
                    <td><?= $r['selesai'] ?: '-' ?></td>
                    <td><?= $r['ditolak'] ?: '-' ?></td>
                    <td class="bg-light fw-bold"><?= $r['total'] ?: '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <td class="text-start ps-3 fw-bold">TOTAL TAHUN INI</td>
                    <td><?= $total_tahun['masuk'] ?></td>
                    <td><?= $total_tahun['proses'] ?></td>
                    <td><?= $total_tahun['approval'] ?></td>
                    <td><?= $total_tahun['selesai'] ?></td>
                    <td><?= $total_tahun['ditolak'] ?></td>
                    <td class="fs-5"><?= $total_tahun['total'] ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?= $this->endSection() ?>