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
            <!-- Tambahkan onsubmit="showLoading()" pada tag form -->
            <form action="/pelapor/simpan" method="post" enctype="multipart/form-data" onsubmit="showLoading()">
                
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

                <!-- Bagian Input Foto -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Foto Kondisi <span class="text-danger">*</span></label>
                    <input class="form-control form-control-lg" type="file" id="foto_kondisi" name="foto_kondisi" accept="image/*" required onchange="previewImage(this)">
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-info-circle text-primary me-1"></i>Ketuk kolom di atas. HP Anda akan otomatis menampilkan pilihan <b>Kamera</b> atau <b>Galeri</b>.
                    </small>

                    <!-- Tempat Tampil Preview Gambar -->
                    <div id="preview-container" class="mt-3 text-center d-none p-3 border rounded bg-light">
                        <img id="img-preview" src="" class="img-fluid rounded shadow-sm mb-2" style="max-height: 250px; object-fit: contain;">
                        <p id="file-name" class="small text-muted mb-0 fw-bold"></p>
                    </div>
                </div>

                <!-- Tambahkan ID pada tombol submit -->
                <button type="submit" id="btn-submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="fas fa-paper-plane me-2"></i> Kirim Laporan
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- ELEMENT LOADING OVERLAY (Bawaan disembunyikan) -->
    <!-- ============================================== -->
    <div id="loading-overlay" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.85); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <h5 class="mt-3 fw-bold text-primary">Mengirim Laporan...</h5>
        <p class="text-muted small mb-0">Sedang mengunggah foto ke Google Drive.</p>
        <p class="text-muted small">Mohon jangan tutup layar ini.</p>
    </div>

    <!-- ============================================== -->
    <!-- SCRIPT PREVIEW GAMBAR & LOADING ANIMATION      -->
    <!-- ============================================== -->
    <script>
        // Fungsi Preview Gambar
        function previewImage(input) {
            const previewContainer = document.getElementById('preview-container');
            const imgPreview = document.getElementById('img-preview');
            const fileName = document.getElementById('file-name');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                imgPreview.src = URL.createObjectURL(file);
                fileName.textContent = file.name;
                previewContainer.classList.remove('d-none');
            } else {
                previewContainer.classList.add('d-none');
                imgPreview.src = '';
                fileName.textContent = '';
            }
        }

        // Fungsi Menampilkan Loading & Mematikan Tombol Submit
        function showLoading() {
            // 1. Tampilkan overlay animasi loading layar penuh
            const overlay = document.getElementById('loading-overlay');
            overlay.classList.remove('d-none');

            // 2. Ubah tombol "Kirim" menjadi "Memproses..." dan matikan (disabled)
            const btnSubmit = document.getElementById('btn-submit');
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses...';
            
            // Catatan: Kita tidak langsung men-disable tombol menggunakan btnSubmit.disabled = true; 
            // Karena pada beberapa browser, mendisable tombol submit saat disubmit akan membatalkan pengiriman form.
            // Sebagai gantinya, style tombol dibuat seolah-olah disabled menggunakan CSS "pointer-events: none; opacity: 0.7;"
            btnSubmit.style.pointerEvents = 'none';
            btnSubmit.style.opacity = '0.7';
        }
    </script>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/pelapor/dashboard"><i class="fas fa-home"></i>Beranda</a>
    <a href="/pelapor/buat" class="active"><i class="fas fa-plus-circle text-primary fs-3"></i>Lapor</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>