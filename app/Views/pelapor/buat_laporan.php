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
            <!-- PENTING: Form onSubmit sekarang memanggil validasiDanLoading -->
            <form action="/pelapor/simpan" method="post" enctype="multipart/form-data" onsubmit="return validasiDanLoading(event)">
                
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

                <!-- ======================================================= -->
                <!-- BAGIAN INPUT FOTO (2 TOMBOL NATIVE HTML & BEBAS ERROR)  -->
                <!-- ======================================================= -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Foto Kondisi <span class="text-danger">*</span></label>
                    
                    <!-- 1. Input Khusus Kamera (Memiliki capture) -->
                    <input type="file" id="input_kamera" accept="image/*" capture="environment" class="d-none" onchange="prosesFoto(this, 'input_galeri')">
                    
                    <!-- 2. Input Khusus Galeri (Tanpa capture) -->
                    <input type="file" id="input_galeri" accept="image/*" class="d-none" onchange="prosesFoto(this, 'input_kamera')">
                    
                    <!-- Tombol UI (Menggunakan atribut for="..." untuk memicu input tanpa klik JS) -->
                    <div class="d-flex gap-2 mb-2">
                        <label for="input_kamera" class="btn btn-outline-primary flex-fill fw-bold py-2 m-0 shadow-sm cursor-pointer">
                            <i class="fas fa-camera me-1"></i> Buka Kamera
                        </label>
                        <label for="input_galeri" class="btn btn-outline-success flex-fill fw-bold py-2 m-0 shadow-sm cursor-pointer">
                            <i class="fas fa-images me-1"></i> Pilih Galeri
                        </label>
                    </div>

                    <!-- Tempat Tampil Preview Gambar -->
                    <div id="preview-container" class="mt-3 text-center d-none p-3 border rounded bg-light">
                        <img id="img-preview" src="" class="img-fluid rounded shadow-sm mb-2" style="max-height: 250px; object-fit: contain;">
                        <p id="file-name" class="small text-muted mb-0 fw-bold"></p>
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="btn btn-primary w-100 py-2 fw-bold">
                    <i class="fas fa-paper-plane me-2"></i> Kirim Laporan
                </button>
            </form>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- ELEMENT LOADING OVERLAY                        -->
    <!-- ============================================== -->
    <div id="loading-overlay" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.85); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <h5 class="mt-3 fw-bold text-primary">Mengirim Laporan...</h5>
        <p class="text-muted small mb-0">Sedang mengunggah foto ke Google Drive.</p>
        <p class="text-muted small">Mohon jangan tutup layar ini.</p>
    </div>

    <!-- ============================================== -->
    <!-- SCRIPT PREVIEW & VALIDASI (SANGAT RINGAN)      -->
    <!-- ============================================== -->
    <script>
        // Fungsi mengatur preview dan menentukan input mana yang dikirim ke server
        function prosesFoto(inputTerpilih, idInputLain) {
            if (inputTerpilih.files.length === 0) return; // Batal pilih

            const inputLain = document.getElementById(idInputLain);
            const file = inputTerpilih.files[0];

            // 1. Pasang nama agar input ini yang ditangkap oleh $this->request->getFile('foto_kondisi') di CI4
            inputTerpilih.name = 'foto_kondisi';
            
            // 2. Kosongkan input sebelahnya agar file lama tidak menumpuk/bentrok
            inputLain.removeAttribute('name');
            inputLain.value = ''; 

            // 3. Render gambar menggunakan ObjectURL (Sangat cepat dan anti-crash di HP)
            document.getElementById('img-preview').src = URL.createObjectURL(file);
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('preview-container').classList.remove('d-none');
        }

        // Fungsi mengecek apakah foto sudah diisi sebelum loading muncul
        function validasiDanLoading(event) {
            const jumlahKamera = document.getElementById('input_kamera').files.length;
            const jumlahGaleri = document.getElementById('input_galeri').files.length;

            // Karena kita menggunakan d-none, atribut HTML 'required' tidak bisa dipakai.
            // Maka kita gunakan validasi manual JavaScript di sini:
            if (jumlahKamera === 0 && jumlahGaleri === 0) {
                event.preventDefault(); // Batalkan form submit
                alert('Ups! Anda belum melampirkan foto. Silakan ketuk "Buka Kamera" atau "Pilih Galeri".');
                return false;
            }

            // Jika valid, tampilkan Loading
            document.getElementById('loading-overlay').classList.remove('d-none');
            
            const btnSubmit = document.getElementById('btn-submit');
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            btnSubmit.style.pointerEvents = 'none';
            btnSubmit.style.opacity = '0.7';
            
            return true;
        }
    </script>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/pelapor/dashboard"><i class="fas fa-home"></i>Beranda</a>
    <a href="/pelapor/buat" class="active"><i class="fas fa-plus-circle text-primary fs-3"></i>Lapor</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>