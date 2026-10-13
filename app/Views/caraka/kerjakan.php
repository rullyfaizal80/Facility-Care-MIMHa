<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <a href="/caraka/dashboard" class="text-secondary me-3"><i class="fas fa-arrow-left fs-5"></i></a>
        <h5 class="m-0 fw-bold">Detail Pekerjaan</h5>
    </div>

    <!-- app/Views/caraka/kerjakan.php -->

    <!-- Informasi Tugas -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <h6 class="fw-bold text-primary"><?= esc($tiket['judul_laporan']) ?></h6>
            <p class="mb-1 small"><i class="fas fa-map-marker-alt text-muted"></i> <?= esc($tiket['lokasi_manual']) ?></p>
            <hr>
            <p class="small mb-3"><strong>Keluhan:</strong><br><?= esc($tiket['deskripsi']) ?></p>

            <!-- Menampilkan Foto Awal dari Pelapor -->
            <small class="fw-bold d-block mb-2"><i class="fas fa-image text-muted"></i> Foto Kondisi Awal:</small>
            <?php if($foto_sebelum): ?>
                <?php $nama_file = str_replace('LOKAL_', '', $foto_sebelum['google_file_id']); ?>
                <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" 
                     class="img-fluid rounded border shadow-sm" 
                     alt="Foto Awal" 
                     style="max-height: 200px; cursor: zoom-in;"
                     onclick="showImage(this.src)">
            <?php else: ?>
                <div class="bg-white p-3 text-center text-muted small rounded border">
                    <i class="fas fa-image fa-2x mb-1 text-light"></i><br>Tidak ada foto dilampirkan
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form Upload Hasil -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="/caraka/selesaikan" method="post" enctype="multipart/form-data">
                <input type="hidden" name="tiket_id" value="<?= $tiket['id'] ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold"><i class="fas fa-camera"></i> Foto Hasil Perbaikan <span class="text-danger">*</span></label>
                    <!-- capture="environment" untuk buka kamera belakang -->
                    <input class="form-control form-control-lg" type="file" name="foto_sesudah" accept="image/*" capture="environment" required>
                    <small class="text-muted d-block mt-2">Wajib difoto sebagai bukti perbaikan selesai.</small>
                </div>

                <div class="form-check mb-4 mt-3">
                    <input class="form-check-input" type="checkbox" id="ceklisSelesai" required>
                    <label class="form-check-label small" for="ceklisSelesai">
                        Saya menyatakan bahwa fasilitas ini telah diperbaiki dan berfungsi dengan baik.
                    </label>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-bold"><i class="fas fa-check-circle me-1"></i> Kirim Laporan Selesai</button>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/caraka/dashboard" class="active"><i class="fas fa-home"></i>Beranda</a>
    <a href="#"><i class="fas fa-clipboard-list"></i>Riwayat</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>