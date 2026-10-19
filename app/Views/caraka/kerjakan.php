<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
  <a href="/caraka/dashboard" class="text-secondary me-3">
    <i class="fas fa-arrow-left fs-5"></i>
  </a>
  <h5 class="m-0 fw-bold">Detail Pekerjaan</h5>
</div>

<!-- Informasi Tugas -->
<div class="card shadow-sm border-0 mb-4 bg-light">
  <div class="card-body">
    <h6 class="fw-bold text-primary"><?= esc($tiket['judul_laporan']) ?></h6>
    <p class="mb-1 small">
      <i class="fas fa-map-marker-alt text-muted"></i> <?= esc($tiket['lokasi_manual']) ?>
    </p>
    <hr />
    <p class="small mb-3">
      <strong>Keluhan:</strong><br /><?= esc($tiket['deskripsi']) ?>
    </p>

    <!-- Menampilkan Foto Awal dari Pelapor -->
    <small class="fw-bold d-block mb-2">
      <i class="fas fa-image text-muted"></i> Foto Kondisi Awal:
    </small>
    <?php if(!empty($foto_sebelum)): ?>
    <img
      loading="lazy"
      src="<?= base_url('caraka/renderFoto/' . esc($foto_sebelum['google_file_id'])) ?>"
      class="img-fluid rounded border shadow-sm"
      alt="Foto Awal"
      style="max-height: 200px; cursor: zoom-in"
      onclick="showImage(this.src)"
    />
    <?php else: ?>
    <div class="bg-white p-3 text-center text-muted small rounded border">
      <i class="fas fa-image fa-2x mb-1 text-light"></i><br />Tidak ada foto dilampirkan
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Form Upload Hasil -->
<div class="card shadow-sm border-0 mb-4">
  <div class="card-body">
    <?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
      <i class="fas fa-exclamation-triangle me-1"></i> <?= session()->getFlashdata('error') ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <form action="/caraka/selesaikan" method="post" enctype="multipart/form-data" onsubmit="return validasiDanLoading(event)">
      <input type="hidden" name="tiket_id" value="<?= $tiket['id'] ?>" />

      <!-- Bagian Input Foto -->
      <div class="mb-4">
        <label class="form-label fw-bold">
          <i class="fas fa-camera"></i> Foto Hasil Perbaikan <span class="text-danger">*</span>
        </label>
        
        <!-- 1. Input Khusus Kamera (ID disamakan persis dengan buat_laporan) -->
        <input type="file" id="input_kamera" accept="image/*" capture="environment" class="d-none" onchange="prosesFoto(this, 'input_galeri')">
        
        <!-- 2. Input Khusus Galeri (ID disamakan persis dengan buat_laporan) -->
        <input type="file" id="input_galeri" accept="image/*" class="d-none" onchange="prosesFoto(this, 'input_kamera')">
        
        <!-- Tombol UI -->
        <div class="d-flex gap-2 mb-2">
            <label for="input_kamera" class="btn btn-outline-primary flex-fill fw-bold py-2 m-0 shadow-sm cursor-pointer">
                <i class="fas fa-camera me-1"></i> Buka Kamera
            </label>
            <label for="input_galeri" class="btn btn-outline-success flex-fill fw-bold py-2 m-0 shadow-sm cursor-pointer">
                <i class="fas fa-images me-1"></i> Pilih Galeri
            </label>
        </div>

        <small class="text-muted d-block mt-2">Wajib difoto sebagai bukti perbaikan selesai.</small>

        <!-- Tempat Tampil Preview Gambar -->
        <div id="preview-container" class="mt-3 text-center d-none p-3 border rounded bg-light">
            <img id="img-preview" src="" class="img-fluid rounded shadow-sm mb-2" style="max-height: 250px; object-fit: contain;">
            <p id="file-name" class="small text-muted mb-0 fw-bold"></p>
        </div>
      </div>

      <button type="submit" id="btn-submit" class="btn btn-success w-100 py-2 fw-bold">
        <i class="fas fa-check-circle me-1"></i> Kirim Laporan Selesai
      </button>
    </form>
  </div>
</div>

<!-- ============================================== -->
<!-- ELEMENT LOADING OVERLAY                        -->
<!-- ============================================== -->
<div id="loading-overlay" class="d-none" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(255, 255, 255, 0.85); z-index: 9999; display: flex; flex-direction: column; justify-content: center; align-items: center; backdrop-filter: blur(3px);">
    <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;"></div>
    <h5 class="mt-3 fw-bold text-success">Mengirim Hasil...</h5>
    <p class="text-muted small mb-0">Sedang mengunggah bukti ke sistem.</p>
    <p class="text-muted small">Mohon jangan tutup layar ini.</p>
</div>

<!-- Modal Zoom-In Gambar (Awal) -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0 shadow-none">
      <div class="modal-header border-0 pb-0 justify-content-end">
        <button
          type="button"
          class="btn-close btn-close-white fs-4"
          data-bs-dismiss="modal"
          aria-label="Close"
          style="background-color: white; border-radius: 50%; padding: 0.5rem"
        ></button>
      </div>
      <div class="modal-body text-center pt-2">
        <img
          loading="lazy"
          id="enlargedImage"
          src=""
          class="img-fluid rounded shadow-lg"
          style="max-height: 85vh; object-fit: contain"
        />
      </div>
    </div>
  </div>
</div>

<!-- ============================================== -->
<!-- SCRIPT PREVIEW & VALIDASI (SAMA PERSIS)        -->
<!-- ============================================== -->
<script>
  function showImage(src) {
    document.getElementById("enlargedImage").src = src;
    const viewerModal = new bootstrap.Modal(
      document.getElementById("imageViewerModal"),
    );
    viewerModal.show();
  }

  // Fungsi mengatur preview dan menentukan input mana yang dikirim ke server
  function prosesFoto(inputTerpilih, idInputLain) {
      if (inputTerpilih.files.length === 0) return; // Batal pilih

      const inputLain = document.getElementById(idInputLain);
      const file = inputTerpilih.files[0];

      // PENTING: Untuk modul Caraka, input ini harus diberi nama 'foto_sesudah'
      inputTerpilih.name = 'foto_sesudah';
      
      // Kosongkan input sebelahnya agar file lama tidak menumpuk/bentrok
      inputLain.removeAttribute('name');
      inputLain.value = ''; 

      // Render gambar menggunakan ObjectURL (Sangat cepat dan anti-crash di HP)
      document.getElementById('img-preview').src = URL.createObjectURL(file);
      document.getElementById('file-name').textContent = file.name;
      document.getElementById('preview-container').classList.remove('d-none');
  }

  // Fungsi mengecek apakah foto sudah diisi sebelum loading muncul
  function validasiDanLoading(event) {
      const jumlahKamera = document.getElementById('input_kamera').files.length;
      const jumlahGaleri = document.getElementById('input_galeri').files.length;

      // Validasi manual JavaScript:
      if (jumlahKamera === 0 && jumlahGaleri === 0) {
          event.preventDefault(); // Batalkan form submit
          alert('Ups! Anda belum melampirkan foto hasil perbaikan. Silakan ketuk "Buka Kamera" atau "Pilih Galeri".');
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

<!-- Tag endSection dipindahkan ke sini agar mencakup Modal dan Script -->
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
<a href="/caraka/dashboard" class="active"><i class="fas fa-home"></i>Beranda</a>
<a href="/caraka/riwayat"><i class="fas fa-clipboard-list"></i>Riwayat</a>
<a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>