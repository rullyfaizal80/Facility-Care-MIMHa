<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex align-items-center mb-4">
        <a href="/pelapor/dashboard" class="text-secondary me-3"><i class="fas fa-arrow-left fs-5"></i></a>
        <h5 class="m-0 fw-bold">Buat Laporan Baru</h5>
    </div>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="/pelapor/simpan" method="post" enctype="multipart/form-data">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Lokasi / Fasilitas <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="lokasi_manual" placeholder="Contoh: AC Ruang Guru Lantai 1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Ringkasan Masalah <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="judul_laporan" placeholder="Contoh: AC Bocor, Lampu Mati" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Deskripsi Detail</label>
                    <textarea class="form-control" name="deskripsi" rows="3" placeholder="Jelaskan kondisi kerusakan lebih lanjut..."></textarea>
                </div>

                <!-- Bagian Input Foto (Wajib) -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Foto Kondisi <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="foto_kondisi" name="foto_kondisi" accept="image/*" capture="environment" required>
                    <small class="text-muted mt-1 d-block">Wajib mengambil foto langsung atau memilih dari galeri.</small>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="fas fa-paper-plane me-2"></i> Kirim Laporan</button>
            </form>
        </div>
    </div>
<?= $this->endSection() ?>

<!-- Bottom nav tidak perlu ditampilkan di form untuk mengurangi distraksi layar -->
<?= $this->section('bottom_nav') ?>
    <a href="/pelapor/dashboard"><i class="fas fa-home"></i>Beranda</a>
    <a href="/pelapor/buat" class="active"><i class="fas fa-plus-circle text-primary fs-3"></i>Lapor</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>