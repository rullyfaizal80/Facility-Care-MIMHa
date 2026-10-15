<?= $this->extend('layouts/mobile_layout') ?> <?= $this->section('content') ?>
<div class="d-flex align-items-center mb-4">
  <a href="/caraka/riwayat" class="text-secondary me-3"
    ><i class="fas fa-arrow-left fs-5"></i
  ></a>
  <h5 class="m-0 fw-bold">Detail Pekerjaan Selesai</h5>
</div>

<!-- INFO TUGAS -->
<div class="card shadow-sm border-0 mb-3 bg-light">
  <div class="card-body">
    <h6 class="fw-bold text-success mb-2">
      <i class="fas fa-check-double"></i> <?= esc($tiket['judul_laporan']) ?>
    </h6>
    <p class="mb-1 small">
      <i class="fas fa-map-marker-alt text-muted me-1"></i> <?=
      esc($tiket['lokasi_manual']) ?>
    </p>
    <p class="mb-2 small">
      <i class="fas fa-user text-muted me-1"></i> Dilaporkan oleh: <?=
      esc($tiket['nama_pelapor'] ?? 'Sistem') ?>
    </p>
    <hr class="my-2" />
    <p class="small mb-1">
      <strong>Masalah:</strong><br /><?= esc($tiket['deskripsi']) ?>
    </p>
  </div>
</div>

<!-- CATATAN ADMIN (Apresiasi / Final Note) -->
<?php if(!empty($tiket['catatan_admin'])): ?>
<div
  class="card shadow-sm border-0 mb-3 border-start border-primary border-4 bg-light"
>
  <div class="card-body p-2">
    <small class="fw-bold text-primary"
      ><i class="fas fa-comment-dots"></i> Catatan Admin (Reviewer):</small
    >
    <p class="small mb-0 mt-1"><?= esc($tiket['catatan_admin']) ?></p>
  </div>
</div>
<?php endif; ?>

<!-- FOTO BUKTI -->
<div class="row g-2 mb-4">
  <div class="col-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body p-2 text-center">
        <small class="text-muted d-block mb-1 fw-bold">Kondisi Awal</small>
        <?php if(!empty($foto_sebelum)): ?>
        <img
          loading="lazy"
          src="<?= base_url('caraka/renderFoto/' . esc($foto_sebelum['google_file_id'])) ?>"
          class="img-fluid rounded border"
          alt="Foto Sebelum"
          style="cursor: zoom-in"
          onclick="showImage(this.src)"
        />
        <?php else: ?>
        <div class="bg-light rounded py-3 text-muted small border">
          <i class="fas fa-image fa-2x mb-1"></i><br />Tidak ada foto
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-6">
    <div class="card shadow-sm border-0 h-100">
      <div class="card-body p-2 text-center">
        <small class="text-success d-block mb-1 fw-bold">Hasil Anda</small>
        <?php if(!empty($foto_sesudah)): ?>
        <img
          loading="lazy"
          src="<?= base_url('caraka/renderFoto/' . esc($foto_sesudah['google_file_id'])) ?>"
          class="img-fluid rounded border"
          alt="Foto Sesudah"
          style="cursor: zoom-in"
          onclick="showImage(this.src)"
        />
        <?php else: ?>
        <div class="bg-light rounded py-3 text-muted small border">
          <i class="fas fa-tools fa-2x mb-1"></i><br />Belum ada foto
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<!-- Sembunyikan bottom_nav di tampilan detail -->
<?= $this->section('bottom_nav') ?>
<a href="/caraka/dashboard"><i class="fas fa-home"></i>Beranda</a>
<a href="/caraka/riwayat" class="active"
  ><i class="fas fa-clipboard-list"></i>Riwayat</a
>
<a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>
