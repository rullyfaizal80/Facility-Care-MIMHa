<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item"><a class="nav-link" href="/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link active" href="/admin/tiket"><i class="fas fa-ticket-alt me-2"></i> Kelola Tiket</a></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Detail Laporan: <?= esc($tiket['judul_laporan']) ?></h1>
        <a href="/admin/tiket" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="row">
        <!-- Kolom Info Tiket (Kiri) -->
        <div class="col-md-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-bold"><i class="fas fa-info-circle text-primary"></i> Informasi Laporan</div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td width="30%" class="text-muted">Pelapor</td><td>: <?= esc($tiket['nama_pelapor'] ?? 'Sistem') ?></td></tr>
                        <tr><td class="text-muted">Fasilitas</td><td>: <?= esc($tiket['nama_fasilitas']) ?> (<?= esc($tiket['lokasi']) ?>)</td></tr>
                        <tr><td class="text-muted">Tanggal</td><td>: <?= date('d M Y, H:i', strtotime($tiket['tgl_dibuat'])) ?></td></tr>
                        <tr><td class="text-muted">Keluhan</td><td>: <?= esc($tiket['deskripsi']) ?></td></tr>
                        <tr><td class="text-muted">Teknisi</td><td>: <strong><?= esc($tiket['nama_caraka'] ?? '-') ?></strong></td></tr>
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
                                <label class="form-label small">Catatan (Opsional, wajib diisi jika ditolak)</label>
                                <textarea class="form-control" name="catatan_admin" rows="2"></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="aksi" value="reject" class="btn btn-outline-danger me-md-2" onclick="return confirm('Kembalikan tugas ini ke teknisi?')"><i class="fas fa-times"></i> Tolak / Revisi</button>
                                <button type="submit" name="aksi" value="approve" class="btn btn-success"><i class="fas fa-check"></i> Setujui Laporan</button>
                            </div>
                        </form>
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
                                <!-- Karena kita masih pakai lokal (ID: LOKAL_namafile.jpg), kita ekstrak nama filenya -->
                                <?php $nama_file = str_replace('LOKAL_', '', $foto_sebelum['google_file_id']); ?>
                                <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" class="img-fluid rounded" alt="Foto Sebelum" style="max-height: 300px; object-fit: contain;">
                            <?php else: ?>
                                <div class="text-muted py-5"><i class="fas fa-image fa-3x mb-2"></i><br>Tidak ada foto dari pelapor</div>
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
                                <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" class="img-fluid rounded" alt="Foto Sesudah" style="max-height: 300px; object-fit: contain;">
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