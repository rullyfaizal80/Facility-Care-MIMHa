<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item"><a class="nav-link" href="/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/tiket"><i class="fas fa-ticket-alt me-2"></i> Kelola Tiket</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/laporan"><i class="fas fa-print me-2"></i> Rekap & Cetak</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/user"><i class="fas fa-users me-2"></i> Kelola User</a></li>
    <li class="nav-item"><a class="nav-link active" href="/admin/laporan_saya"><i class="fas fa-bullhorn me-2"></i> Laporan Saya</a></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Laporan Saya</h1>
        <button type="button" class="btn btn-primary rounded-pill shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i> Buat Laporan
        </button>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- FILTER BULAN DAN TAHUN -->
    <form method="get" action="/admin/laporan_saya" class="bg-light p-3 rounded border mb-4 shadow-sm d-flex gap-2 align-items-center flex-wrap">
        <span class="fw-bold small text-muted"><i class="fas fa-filter"></i> Filter:</span>
        
        <select name="bulan" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <?php 
            $arrBulan = [
                '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
                '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', 
                '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
            ];
            foreach($arrBulan as $k => $v): 
                $sel = ($k == $bulan) ? 'selected' : '';
            ?>
                <option value="<?= $k ?>" <?= $sel ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>

        <select name="tahun" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <?php 
            $thn_sekarang = date('Y');
            for($t = $thn_sekarang - 4; $t <= $thn_sekarang; $t++): 
                $sel = ($t == $tahun) ? 'selected' : '';
            ?>
                <option value="<?= $t ?>" <?= $sel ?>><?= $t ?></option>
            <?php endfor; ?>
        </select>
    </form>

    <!-- Tampilan Card List -->
    <div class="row">
        <?php if(empty($tiket)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <div class="bg-light rounded-circle d-inline-block p-4 mb-3 shadow-sm">
                    <i class="fas fa-box-open fa-3x text-secondary"></i>
                </div>
                <h6 class="fw-bold">Belum ada laporan</h6>
                <p class="small">Tidak ada laporan kerusakan yang Anda buat pada periode bulan/tahun ini.</p>
            </div>
        <?php else: ?>
            <?php foreach($tiket as $t): ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold text-primary text-truncate mb-0" style="max-width: 70%;"><?= esc($t['judul_laporan']) ?></h6>
                                <?php 
                                    $bg = 'bg-secondary';
                                    if($t['status'] == 'menunggu_respon') $bg = 'bg-warning text-dark';
                                    if($t['status'] == 'dikerjakan') $bg = 'bg-info text-dark';
                                    if($t['status'] == 'pending') $bg = 'bg-danger'; 
                                    if($t['status'] == 'menunggu_approval') $bg = 'bg-primary';
                                    if($t['status'] == 'selesai') $bg = 'bg-success';
                                    if($t['status'] == 'ditolak') $bg = 'bg-dark'; 
                                ?>
                                <span class="badge <?= $bg ?> rounded-pill" style="font-size: 0.65rem;"><?= str_replace('_', ' ', strtoupper($t['status'])) ?></span>
                            </div>
                            <p class="small text-muted mb-1"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?= esc($t['lokasi_manual']) ?></p>
                            <p class="small text-muted mb-3"><i class="fas fa-clock me-2 text-secondary"></i><?= date('d M Y, H:i', strtotime($t['tgl_dibuat'])) ?></p>
                            
                            <a href="/admin/tiket/detail/<?= $t['id'] ?>?source=laporansaya" class="btn btn-sm btn-outline-secondary w-100 fw-bold">
                                <i class="fas fa-eye me-1"></i> Pantau Proses
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- MODAL FORM BUAT LAPORAN -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            
            <!-- PERBAIKAN: Tag <form> sekarang digabung dengan class "modal-content" -->
            <form action="/admin/laporan_saya/simpan" method="post" enctype="multipart/form-data" onsubmit="return validasiDanLoadingAdmin(event)" class="modal-content border-0 shadow-lg">
                
                <div class="modal-header bg-white border-bottom px-4 pt-4">
                    <h5 class="modal-title fw-bold">Buat Laporan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- modal-body sekarang adalah anak langsung dari form (modal-content) -->
                <!-- Ini akan membuat scroll berfungsi sempurna -->
                <div class="modal-body px-4 py-3">
                    
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

                    <!-- BAGIAN INPUT FOTO ADMIN (DI-UPGRADE) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Foto Kondisi <span class="text-danger">*</span></label>
                        
                        <!-- 1. Input Khusus Kamera -->
                        <input type="file" id="input_kamera_admin" accept="image/*" capture="environment" class="d-none" onchange="prosesFotoAdmin(this, 'input_galeri_admin')">
                        
                        <!-- 2. Input Khusus Galeri -->
                        <input type="file" id="input_galeri_admin" accept="image/*" class="d-none" onchange="prosesFotoAdmin(this, 'input_kamera_admin')">
                        
                        <!-- Tombol UI -->
                        <div class="d-flex gap-2 mb-2">
                            <label for="input_kamera_admin" class="btn btn-outline-primary flex-fill fw-bold py-2 m-0 shadow-sm cursor-pointer">
                                <i class="fas fa-camera me-1"></i> Kamera
                            </label>
                            <label for="input_galeri_admin" class="btn btn-outline-success flex-fill fw-bold py-2 m-0 shadow-sm cursor-pointer">
                                <i class="fas fa-images me-1"></i> Galeri
                            </label>
                        </div>

                        <small class="text-muted d-block mt-2">Wajib mengambil foto langsung atau memilih dari galeri.</small>

                        <!-- Tempat Tampil Preview Gambar -->
                        <div id="preview-container-admin" class="mt-3 text-center d-none p-3 border rounded bg-light">
                            <img id="img-preview-admin" src="" class="img-fluid rounded shadow-sm mb-2" style="max-height: 250px; object-fit: contain;">
                            <p id="file-name-admin" class="small text-muted mb-0 fw-bold"></p>
                        </div>
                    </div>

                </div>
                
                <div class="modal-footer bg-white border-top px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <!-- Tambahkan id pada tombol submit -->
                    <button type="submit" id="btn-submit-admin" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fas fa-paper-plane me-2"></i> Kirim Laporan</button>
                </div>
            </form>
            <!-- Selesai Tag Form -->

        </div>
    </div>

    <!-- ============================================== -->
    <!-- ELEMENT LOADING OVERLAY KHUSUS ADMIN           -->
    <!-- Z-index dibuat 1060 agar menimpa Modal         -->
    <!-- ============================================== -->
    <div id="loading-overlay-admin" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.85); z-index: 1060; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <h5 class="mt-3 fw-bold text-primary">Mengirim Laporan...</h5>
        <p class="text-muted small mb-0">Sedang mengunggah foto ke Google Drive.</p>
        <p class="text-muted small">Mohon jangan tutup layar ini.</p>
    </div>

    <!-- ============================================== -->
    <!-- SCRIPT JS UNTUK PREVIEW & LOADING ADMIN        -->
    <!-- ============================================== -->
    <script>
        // Fungsi mengatur preview dan menentukan input
        function prosesFotoAdmin(inputTerpilih, idInputLain) {
            if (inputTerpilih.files.length === 0) return; 

            const inputLain = document.getElementById(idInputLain);
            const file = inputTerpilih.files[0];

            // PENTING: Untuk modul Admin, backend menangkap dengan name 'foto'
            inputTerpilih.name = 'foto';
            
            // Kosongkan input sebelahnya
            inputLain.removeAttribute('name');
            inputLain.value = ''; 

            // Render gambar menggunakan ObjectURL
            document.getElementById('img-preview-admin').src = URL.createObjectURL(file);
            document.getElementById('file-name-admin').textContent = file.name;
            document.getElementById('preview-container-admin').classList.remove('d-none');
        }

        // Fungsi mengecek validasi dan memunculkan Loading
        function validasiDanLoadingAdmin(event) {
            const jumlahKamera = document.getElementById('input_kamera_admin').files.length;
            const jumlahGaleri = document.getElementById('input_galeri_admin').files.length;

            if (jumlahKamera === 0 && jumlahGaleri === 0) {
                event.preventDefault(); // Batalkan form submit
                alert('Ups! Anda belum melampirkan foto. Silakan ketuk "Kamera" atau "Galeri".');
                return false;
            }

            // Tampilkan Animasi Loading di atas Modal
            document.getElementById('loading-overlay-admin').classList.remove('d-none');
            
            // Ubah State Tombol Submit
            const btnSubmit = document.getElementById('btn-submit-admin');
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            btnSubmit.style.pointerEvents = 'none';
            btnSubmit.style.opacity = '0.7';
            
            return true;
        }
    </script>
<?= $this->endSection() ?>