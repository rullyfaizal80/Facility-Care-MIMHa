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

    <div class="row">
        <!-- Card Menunggu Respon -->
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-dark"><i class="fas fa-exclamation-circle"></i> Menunggu Respon</h6>
                    <p class="card-text display-5 text-dark fw-bold"><?= $tiket_masuk ?></p>
                </div>
            </div>
        </div>
        <!-- Card Dikerjakan -->
        <div class="col-md-4">
            <div class="card text-white bg-info mb-3 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-dark"><i class="fas fa-tools"></i> Sedang Dikerjakan</h6>
                    <p class="card-text display-5 text-dark fw-bold"><?= $tiket_proses ?></p>
                </div>
            </div>
        </div>
        <!-- Card Selesai -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-check-circle"></i> Selesai Bulan Ini</h6>
                    <p class="card-text display-5 fw-bold"><?= $tiket_selesai ?></p>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>