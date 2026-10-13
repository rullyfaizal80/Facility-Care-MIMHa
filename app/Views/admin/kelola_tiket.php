<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item">
        <a class="nav-link" href="/admin/dashboard">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="/admin/tiket">
            <i class="fas fa-ticket-alt me-2"></i> Kelola Tiket
        </a>
    </li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Kelola Tiket Laporan</h1>
    </div>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Pelapor</th>
                        <th>Fasilitas</th>
                        <th>Masalah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tiket as $t): ?>
                        <tr>
                            <td><?= date('d M Y, H:i', strtotime($t['tgl_dibuat'])) ?></td>
                            <td><?= esc($t['nama_pelapor'] ?? 'Sistem (Rutin)') ?></td>
                            <td>
                                <strong><?= esc($t['nama_fasilitas']) ?></strong><br>
                                <small class="text-muted"><?= esc($t['lokasi']) ?></small>
                            </td>
                            <td><?= esc($t['judul_laporan']) ?></td>
                            
                            <!-- KOLOM STATUS YANG DIREVISI -->
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($t['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                                    if($t['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                                    if($t['status'] == 'menunggu_approval') $badge = 'bg-primary'; // Warna biru untuk approval
                                    if($t['status'] == 'selesai') $badge = 'bg-success';
                                ?>
                                <span class="badge <?= $badge ?> rounded-pill">
                                    <?= str_replace('_', ' ', strtoupper($t['status'])) ?>
                                </span>
                            </td>
                            
                            <!-- KOLOM AKSI YANG DIREVISI -->
                            <td>
                                <?php if($t['status'] == 'menunggu_respon'): ?>
                                    <!-- Tombol Panggil Modal -->
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssign<?= $t['id'] ?>">
                                        <i class="fas fa-user-plus"></i> Tugaskan
                                    </button>
                                
                                <?php elseif($t['status'] == 'menunggu_approval'): ?>
                                    <!-- Tombol Review Laporan dari Caraka -->
                                    <a href="/admin/tiket/detail/<?= $t['id'] ?>" class="btn btn-sm btn-warning fw-bold text-dark">
                                        <i class="fas fa-search"></i> Review Laporan
                                    </a>
                                
                                <?php elseif($t['status'] == 'selesai'): ?>
                                    <!-- Tombol Lihat Detail Selesai -->
                                    <a href="/admin/tiket/detail/<?= $t['id'] ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check-double"></i> Selesai
                                    </a>
                                
                                <?php else: ?>
                                    <!-- Jika sedang dikerjakan -->
                                    <button class="btn btn-sm btn-outline-secondary" disabled>Sedang Dikerjakan</button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal Tugaskan Caraka untuk baris ini -->
                        <div class="modal fade" id="modalAssign<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="/admin/tiket/tugaskan" method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tugaskan Tiket: <?= esc($t['judul_laporan']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="tiket_id" value="<?= $t['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Pilih Caraka (Teknisi)</label>
                                                <select class="form-select" name="caraka_id" required>
                                                    <option value="">-- Pilih Caraka --</option>
                                                    <?php foreach($caraka as $c): ?>
                                                        <option value="<?= $c['id'] ?>"><?= esc($c['nama_lengkap']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Prioritas</label>
                                                <select class="form-select" name="prioritas" required>
                                                    <option value="rendah">Rendah (Bisa ditunda)</option>
                                                    <option value="sedang" selected>Sedang (Normal)</option>
                                                    <option value="tinggi">Tinggi (Segera / Urgent)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan & Tugaskan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>