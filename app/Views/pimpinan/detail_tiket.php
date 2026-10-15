<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item"><a class="nav-link" href="/pimpinan/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Eksekutif</a></li>
    <li class="nav-item"><a class="nav-link active" href="/pimpinan/laporan"><i class="fas fa-file-alt me-2"></i> Rekap & Cetak</a></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detail Laporan</h1>
        <a href="/pimpinan/laporan" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="row">
        <!-- Kolom Info Tiket (Kiri) -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold"><i class="fas fa-info-circle text-primary"></i> Informasi Laporan</div>
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><?= esc($tiket['judul_laporan']) ?></h5>
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td width="30%" class="text-muted">Pelapor</td><td>: <?= esc($tiket['nama_pelapor'] ?? 'Sistem') ?></td></tr>
                        <tr><td class="text-muted">Lokasi</td><td>: <strong><?= esc($tiket['lokasi_manual']) ?></strong></td></tr>
                        <tr><td class="text-muted">Tgl. Lapor</td><td>: <?= date('d M Y, H:i', strtotime($tiket['tgl_dibuat'])) ?></td></tr>
                        <tr><td class="text-muted">Keluhan</td><td>: <?= esc($tiket['deskripsi']) ?></td></tr>
                        <tr><td class="text-muted">Status</td><td>: 
                            <?php 
                                $badge = 'bg-secondary';
                                if($tiket['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                                if($tiket['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                                if($tiket['status'] == 'pending') $badge = 'bg-danger'; 
                                if($tiket['status'] == 'menunggu_approval') $badge = 'bg-primary';
                                if($tiket['status'] == 'selesai') $badge = 'bg-success';
                                if($tiket['status'] == 'ditolak') $badge = 'bg-dark'; 
                            ?>
                            <span class="badge <?= $badge ?> rounded-pill"><?= str_replace('_', ' ', strtoupper($tiket['status'])) ?></span>
                        </td></tr>
                        <tr><td class="text-muted">Teknisi</td><td>: <?= esc($tiket['nama_caraka']) ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- Menampilkan Catatan Admin jika ada -->
            <?php if(!empty($tiket['catatan_admin'])): ?>
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark"><i class="fas fa-comment-dots text-warning"></i> Catatan Admin / Alasan:</h6>
                    <p class="small mb-0 text-muted"><?= esc($tiket['catatan_admin']) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Foto Bukti (Kanan) -->
        <div class="col-md-7">
            <div class="row">
                <!-- Foto Sebelum (Kondisi Awal) -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white text-center fw-bold text-muted">Kondisi Awal (Pelapor)</div>
                        <div class="card-body text-center p-2">
                            <?php if($foto_sebelum): ?>
                                <?php $nama_file = str_replace('LOKAL_', '', $foto_sebelum['google_file_id']); ?>
                                <img src="<?= base_url('pimpinan/renderFoto/' . esc($foto_sebelum['google_file_id'])) ?>" class="img-fluid rounded border" alt="Foto Sebelum" style="cursor: zoom-in;" onclick="showImage(this.src)">
                            <?php else: ?>
                                <div class="text-muted py-5"><i class="fas fa-image fa-3x mb-2"></i><br>Tidak ada foto awal</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Foto Sesudah (Hasil) -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white text-center fw-bold text-success">Hasil Perbaikan (Teknisi)</div>
                        <div class="card-body text-center p-2">
                            <?php if($foto_sesudah): ?>
                                <?php $nama_file = str_replace('LOKAL_', '', $foto_sesudah['google_file_id']); ?>
                                <img src="<?= base_url('pimpinan/renderFoto/' . esc($foto_sesudah['google_file_id'])) ?>" class="img-fluid rounded border" alt="Foto Sesudah" style="cursor: zoom-in;" onclick="showImage(this.src)">
                            <?php else: ?>
                                <div class="text-muted py-5"><i class="fas fa-camera-slash fa-3x mb-2"></i><br>Belum ada foto penyelesaian</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Zoom-In Gambar -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 shadow-none">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white fs-4" data-bs-dismiss="modal" aria-label="Close" style="background-color: white; border-radius: 50%; padding: 0.5rem;"></button>
            </div>
            <div class="modal-body text-center pt-2">
                <img id="enlargedImage" src="" class="img-fluid rounded shadow-lg" style="max-height: 85vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
    function showImage(src) {
        document.getElementById('enlargedImage').src = src;
        const viewerModal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
        viewerModal.show();
    }
</script>
<?= $this->endSection() ?>