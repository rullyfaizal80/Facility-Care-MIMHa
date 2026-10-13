<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <a href="/pelapor/dashboard" class="text-secondary me-3"><i class="fas fa-arrow-left fs-5"></i></a>
        <h5 class="m-0 fw-bold">Detail Laporan</h5>
    </div>

    <!-- INFO STATUS -->
    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body text-center p-3">
            <h6 class="text-muted small mb-1">Status Laporan</h6>
            <?php 
                $badge = 'bg-secondary';
                if($tiket['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                if($tiket['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                if($tiket['status'] == 'menunggu_approval') $badge = 'bg-primary';
                if($tiket['status'] == 'selesai') $badge = 'bg-success';
            ?>
            <h5 class="badge <?= $badge ?> rounded-pill mb-0 py-2 px-3 fs-6">
                <?= str_replace('_', ' ', strtoupper($tiket['status'])) ?>
            </h5>
        </div>
    </div>

    <!-- INFO DETAIL -->
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h6 class="fw-bold text-primary mb-2"><?= esc($tiket['judul_laporan']) ?></h6>
            
            <p class="mb-1 small"><i class="fas fa-map-marker-alt text-muted me-1"></i> <?= esc($tiket['lokasi_manual']) ?></p>
            <p class="mb-2 small"><i class="fas fa-clock text-muted me-1"></i> Dilaporkan: <?= date('d M Y, H:i', strtotime($tiket['tgl_dibuat'])) ?></p>
            
            <?php if($tiket['tgl_selesai']): ?>
                <p class="mb-2 small text-success fw-bold"><i class="fas fa-check-double me-1"></i> Selesai: <?= date('d M Y, H:i', strtotime($tiket['tgl_selesai'])) ?></p>
            <?php endif; ?>

            <p class="small mt-3 mb-0 border-top pt-2"><strong>Keluhan:</strong><br><?= esc($tiket['deskripsi']) ?></p>
            
            <?php if($tiket['caraka_id']): ?>
                <p class="small mt-2 mb-0"><strong>Dikerjakan oleh:</strong> <?= esc($tiket['nama_caraka']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- CATATAN ADMIN JIKA ADA -->
    <?php if(!empty($tiket['catatan_admin'])): ?>
        <div class="card shadow-sm border-0 mb-3 border-start border-primary border-4 bg-light">
            <div class="card-body p-2">
                <small class="fw-bold text-primary"><i class="fas fa-comment-dots"></i> Catatan Admin:</small>
                <p class="small mb-0 mt-1"><?= esc($tiket['catatan_admin']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- FOTO BUKTI -->
    <div class="row g-2 mb-4">
        <!-- Foto Sebelum (Pelapor) -->
        <div class="col-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-2 text-center">
                    <small class="text-muted d-block mb-1 fw-bold">Kondisi Awal</small>
                    <?php if($foto_sebelum): ?>
                        <?php $nama_file = str_replace('LOKAL_', '', $foto_sebelum['google_file_id']); ?>
                        <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" 
     class="img-fluid rounded border" 
     alt="Foto Sebelum" 
     style="cursor: zoom-in;" 
     onclick="showImage(this.src)">
                    <?php else: ?>
                        <div class="bg-light rounded py-3 text-muted small border"><i class="fas fa-image fa-2x mb-1"></i><br>Tidak ada foto</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Foto Sesudah (Caraka) -->
        <div class="col-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-2 text-center">
                    <small class="text-success d-block mb-1 fw-bold">Hasil Perbaikan</small>
                    <?php if($foto_sesudah): ?>
                        <?php $nama_file = str_replace('LOKAL_', '', $foto_sesudah['google_file_id']); ?>
                        <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" 
     class="img-fluid rounded border" 
     alt="Foto Sesudah" 
     style="cursor: zoom-in;" 
     onclick="showImage(this.src)">
                    <?php else: ?>
                        <div class="bg-light rounded py-3 text-muted small border"><i class="fas fa-tools fa-2x mb-1"></i><br>Belum ada foto</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<!-- Bottom Nav Disembunyikan agar fokus baca detail -->
<?= $this->section('bottom_nav') ?>
    <a href="/pelapor/dashboard" class="active"><i class="fas fa-home"></i>Beranda</a>
    <a href="/pelapor/buat"><i class="fas fa-plus-circle text-primary fs-3"></i>Lapor</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>