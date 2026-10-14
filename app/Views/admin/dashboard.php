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
<?= $this->endSection() ?>