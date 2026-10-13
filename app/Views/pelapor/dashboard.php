<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">Laporan Saya</h5>
    </div>

    <!-- Tampilkan Notifikasi Sukses/Gagal -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(empty($tiket)): ?>
        <div class="text-center mt-5 text-muted">
            <i class="fas fa-clipboard-check fa-3x mb-2"></i>
            <p>Belum ada laporan kerusakan.</p>
        </div>
    <?php else: ?>
        <?php foreach($tiket as $t): ?>
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6 class="card-title fw-bold text-primary"><?= esc($t['judul_laporan']) ?></h6>
                        <?php 
                            // Pewarnaan Badge Status
                            $badge = 'bg-secondary';
                            if($t['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                            if($t['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                            if($t['status'] == 'selesai') $badge = 'bg-success';
                        ?>
                        <span class="badge <?= $badge ?> rounded-pill" style="height: fit-content;"><?= str_replace('_', ' ', strtoupper($t['status'])) ?></span>
                    </div>
                    <p class="card-text text-muted mb-1 small">
                        <i class="fas fa-map-marker-alt me-1"></i> <?= esc($t['nama_fasilitas']) ?> (<?= esc($t['lokasi']) ?>)
                    </p>
                    <p class="card-text small mb-0"><?= esc($t['deskripsi']) ?></p>
                </div>
                <div class="card-footer bg-white text-muted small text-end border-0 pt-0">
                    Dilaporkan: <?= date('d M Y, H:i', strtotime($t['tgl_dibuat'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/pelapor/dashboard" class="active"><i class="fas fa-home"></i>Beranda</a>
    <a href="/pelapor/buat"><i class="fas fa-plus-circle text-primary fs-3"></i>Lapor</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>