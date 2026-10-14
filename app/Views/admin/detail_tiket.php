<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item"><a class="nav-link" href="/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link active" href="/admin/tiket"><i class="fas fa-ticket-alt me-2"></i> Kelola Tiket</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/laporan"><i class="fas fa-print me-2"></i> Rekap & Cetak</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/user"><i class="fas fa-users me-2"></i> Kelola User</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/laporan_saya"><i class="fas fa-bullhorn me-2"></i> Laporan Saya</a></li>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detail Laporan</h1>
        <?php 
         if(isset($_GET['source']) && $_GET['source'] == 'laporan') {
             $url_kembali = '/admin/laporan';
         } elseif(isset($_GET['source']) && $_GET['source'] == 'laporansaya') {
             $url_kembali = '/admin/laporan_saya'; // Tambahan ini
         } else {
             $url_kembali = '/admin/tiket';
         }
     ?>
     <a href="<?= $url_kembali ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
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
                        <tr><td class="text-muted">Teknisi</td><td>: <?= esc($tiket['nama_caraka']) ?></td></tr>
                    </table>
                </div>
            </div>

            <!-- Panel Approval (Hanya muncul jika butuh approval) -->
            <?php if($tiket['status'] == 'menunggu_approval'): ?>
                <div class="card shadow-sm border-0 border-top border-warning border-4 bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Tindakan Admin</h6>
                        <form action="/admin/tiket/approval" method="post">
                            <input type="hidden" name="tiket_id" value="<?= $tiket['id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Catatan (Pujian / Alasan Revisi)</label>
                                <textarea class="form-control" name="catatan_admin" rows="3" placeholder="Contoh: Terima kasih, pekerjaan rapi. / Mohon foto ulang, buram."><?= esc($tiket['catatan_admin'] ?? '') ?></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="aksi" value="reject" class="btn btn-outline-danger me-md-2" onclick="return confirm('Tolak laporan dan kembalikan tugas ini ke teknisi?')"><i class="fas fa-times"></i> Tolak / Revisi</button>
                                <button type="submit" name="aksi" value="approve" class="btn btn-success"><i class="fas fa-check"></i> Setujui Laporan</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Jika tiket sudah selesai -->
            <?php if($tiket['status'] == 'selesai'): ?>
                <div class="card shadow-sm border-0 border-start border-success border-4 bg-light">
                    <div class="card-body p-3">
                        <h6 class="text-success fw-bold"><i class="fas fa-check-double"></i> Laporan Selesai Disetujui</h6>
                        <p class="small mb-1 mt-2 text-muted">Tgl. Selesai: <?= date('d M Y, H:i', strtotime($tiket['tgl_selesai'])) ?></p>
                        <?php if(!empty($tiket['catatan_admin'])): ?>
                            <div class="mt-2 small">
                                <strong>Catatan:</strong><br>
                                <?= esc($tiket['catatan_admin']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Kolom Foto Bukti (Kanan) -->
        <div class="col-md-7">
            <div class="row">
                <!-- Foto Sebelum (Pelapor) -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white text-center fw-bold text-muted">Kondisi Awal (Pelapor)</div>
                        <div class="card-body text-center p-2">
                            <?php if($foto_sebelum): ?>
                                <?php $nama_file = str_replace('LOKAL_', '', $foto_sebelum['google_file_id']); ?>
                                <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" class="img-fluid rounded" alt="Foto Sebelum" style="max-height: 350px; cursor: zoom-in; object-fit: contain;" onclick="showImage(this.src)">
                            <?php else: ?>
                                <div class="text-muted py-5"><i class="fas fa-image fa-3x mb-2"></i><br>Tidak ada foto awal</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- Foto Sesudah (Caraka) -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white text-center fw-bold text-success">Hasil Perbaikan (Teknisi)</div>
                        <div class="card-body text-center p-2">
                            <?php if($foto_sesudah): ?>
                                <?php $nama_file = str_replace('LOKAL_', '', $foto_sesudah['google_file_id']); ?>
                                <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" class="img-fluid rounded" alt="Foto Sesudah" style="max-height: 350px; cursor: zoom-in; object-fit: contain;" onclick="showImage(this.src)">
                            <?php else: ?>
                                <div class="text-muted py-5"><i class="fas fa-camera-slash fa-3x mb-2"></i><br>Belum ada foto penyelesaian</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>