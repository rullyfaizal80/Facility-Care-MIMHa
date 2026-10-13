<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <h5 class="mb-4 fw-bold">Riwayat Pekerjaan Selesai</h5>

    <?php if(empty($tugas)): ?>
        <div class="text-center mt-5 text-muted">
            <i class="fas fa-history fa-3x mb-3 text-secondary"></i>
            <p>Belum ada riwayat pekerjaan yang selesai.</p>
        </div>
    <?php else: ?>
        <?php foreach($tugas as $t): ?>
            <div class="card mb-3 shadow-sm border-0 border-start border-success border-4 opacity-75">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="card-title fw-bold text-dark mb-1"><?= esc($t['judul_laporan']) ?></h6>
                        <span class="badge bg-success rounded-pill"><i class="fas fa-check-double"></i> SELESAI</span>
                    </div>
                    <p class="card-text mb-1 small text-muted">
                        <i class="fas fa-map-marker-alt"></i> <?= esc($t['nama_fasilitas']) ?> (<?= esc($t['lokasi']) ?>)
                    </p>
                    <p class="card-text small mb-2"><?= esc($t['deskripsi']) ?></p>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="fas fa-calendar-check"></i> Disetujui: <?= date('d M Y', strtotime($t['tgl_selesai'])) ?></span>
                    </div>
                    <?php if(!empty($t['catatan_admin'])): ?>
        <div class="alert alert-light p-2 small mb-0 mt-2 border">
            <strong><i class="fas fa-comment-dots text-primary"></i> Catatan Admin:</strong><br>
            <?= esc($t['catatan_admin']) ?>
        </div>
    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/caraka/dashboard"><i class="fas fa-home"></i>Beranda</a>
    <!-- Beri class active pada menu Riwayat -->
    <a href="/caraka/riwayat" class="active"><i class="fas fa-clipboard-list"></i>Riwayat</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>