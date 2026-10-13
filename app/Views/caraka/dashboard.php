<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <h5 class="mb-4 fw-bold">Tugas Hari Ini</h5>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if(empty($tugas)): ?>
        <div class="text-center mt-5 text-muted">
            <i class="fas fa-mug-hot fa-3x mb-3 text-secondary"></i>
            <p>Belum ada tugas baru untuk Anda.<br>Selamat beristirahat!</p>
        </div>
    <?php else: ?>
        <?php foreach($tugas as $t): ?>
            <?php 
                // Warna garis pinggir berdasarkan prioritas
                $borderColor = 'border-primary';
                if($t['prioritas'] == 'tinggi') $borderColor = 'border-danger';
                if($t['prioritas'] == 'rendah') $borderColor = 'border-success';
            ?>
            <div class="card mb-3 shadow-sm border-0 border-start <?= $borderColor ?> border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="card-title fw-bold text-dark mb-1"><?= esc($t['judul_laporan']) ?></h6>
                        <?php if($t['prioritas'] == 'tinggi'): ?>
                            <span class="badge bg-danger rounded-pill" style="font-size: 0.65rem;">URGENT</span>
                        <?php endif; ?>
                    </div>
                    <p class="card-text mb-1 small text-muted"><i class="fas fa-map-marker-alt"></i> <?= esc($t['lokasi_manual']) ?></p>
                    <p class="card-text small mb-3"><?= esc($t['deskripsi']) ?></p>
                    <?php if($t['status'] == 'dikerjakan' && !empty($t['catatan_admin'])): ?>
                        <div class="alert alert-danger p-2 small mb-3 border-0">
                        <strong><i class="fas fa-exclamation-circle"></i> Catatan Revisi:</strong><br>
                        <?= esc($t['catatan_admin']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($t['status'] == 'menunggu_approval'): ?>
                        <button class="btn btn-secondary w-100 btn-sm" disabled>Menunggu Approval Admin</button>
                    <?php else: ?>
                        <a href="/caraka/kerjakan/<?= $t['id'] ?>" class="btn btn-primary w-100 btn-sm fw-bold">Kerjakan Tugas</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/caraka/dashboard" class="active"><i class="fas fa-home"></i>Beranda</a>
    <a href="/caraka/riwayat"><i class="fas fa-clipboard-list"></i>Riwayat</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>