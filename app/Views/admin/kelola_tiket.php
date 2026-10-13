<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item"><a class="nav-link" href="/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link active" href="/admin/tiket"><i class="fas fa-ticket-alt me-2"></i> Kelola Tiket</a></li>
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
        <th>Lokasi</th>
        <th>Masalah</th>
        <th>Status</th>
        <th>Teknisi</th> <!-- Tambah kolom header ini -->
        <th>Aksi</th>
    </tr>
</thead>
                <tbody>
                    <?php foreach($tiket as $t): ?>
                        <tr>
                            <td>
    <div class="small" style="white-space: nowrap;">
        <!-- Baris Lapor -->
        <div class="mb-1 text-dark">
            <i class="fas fa-bullhorn text-muted me-1"></i><?= date('d M Y, H:i', strtotime($t['tgl_dibuat'])) ?>
        </div>
        
        <!-- Baris Selesai -->
        <?php if(!empty($t['tgl_selesai'])): ?>
        <div class="text-success fw-bold">
            <i class="fas fa-check-double me-1"></i><?= date('d M Y, H:i', strtotime($t['tgl_selesai'])) ?>
        </div>
        <?php else: ?>
        <div class="text-muted">
            <i class="fas fa-clock me-1"></i>-
        </div>
        <?php endif; ?>
    </div>
</td>
                            <td><?= esc($t['nama_pelapor'] ?? 'Sistem (Rutin)') ?></td>
                            <td><strong><?= esc($t['lokasi_manual']) ?></strong></td>
                            <td><?= esc($t['judul_laporan']) ?></td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($t['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                                    if($t['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                                    if($t['status'] == 'menunggu_approval') $badge = 'bg-primary';
                                    if($t['status'] == 'selesai') $badge = 'bg-success';
                                ?>
                                <span class="badge <?= $badge ?> rounded-pill"><?= str_replace('_', ' ', strtoupper($t['status'])) ?></span>
                            </td>
                            <td>
    <small class="text-muted fw-bold">
        <?= esc($t['nama_caraka']) ?>
    </small>
</td>
                            <td>
                                <?php if($t['status'] == 'menunggu_respon'): ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssign<?= $t['id'] ?>">
                                        <i class="fas fa-user-plus"></i> Tugaskan
                                    </button>
                                <?php elseif($t['status'] == 'menunggu_approval'): ?>
                                    <a href="/admin/tiket/detail/<?= $t['id'] ?>" class="btn btn-sm btn-warning fw-bold text-dark"><i class="fas fa-search"></i> Review Laporan</a>
                                <?php elseif($t['status'] == 'selesai'): ?>
                                    <a href="/admin/tiket/detail/<?= $t['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-check-double"></i> Selesai</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled>Sedang Dikerjakan</button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- MODAL PENUGASAN (DETAIL & MULTI-CARAKA) -->
                        <div class="modal fade" id="modalAssign<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg"> <!-- Modal diperlebar jadi LG agar rapi -->
                                <div class="modal-content">
                                    <form action="/admin/tiket/tugaskan" method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Penugasan Tiket Baru</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body row">
                                            <input type="hidden" name="tiket_id" value="<?= $t['id'] ?>">
                                            
                                            <!-- KIRI: Detail & Foto Laporan -->
                                            <div class="col-md-6 border-end">
                                                <h6 class="fw-bold text-primary mb-1"><?= esc($t['judul_laporan']) ?></h6>
                                                <p class="small mb-2"><i class="fas fa-map-marker-alt text-muted"></i> <?= esc($t['lokasi_manual']) ?></p>
                                                <p class="small mb-3 text-muted">"<?= esc($t['deskripsi']) ?>"</p>
                                                
                                                <small class="fw-bold d-block mb-1">Foto Kondisi:</small>
                                                <?php if($t['foto_sebelum']): ?>
                                                    <?php $nama_file = str_replace('LOKAL_', '', $t['foto_sebelum']); ?>
                                                    <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" 
                                                         class="img-fluid rounded border" 
                                                         alt="Foto" style="max-height: 180px; cursor: zoom-in;" 
                                                         onclick="showImage(this.src)">
                                                <?php else: ?>
                                                    <div class="bg-light p-3 text-center text-muted small rounded border">Tidak ada foto</div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- KANAN: Form Penugasan -->
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Pilih Caraka (Bisa Lebih dari 1)</label>
                                                    <!-- Tambahkan "multiple" pada select, dan "[]" pada name -->
                                                    <select class="form-select" name="caraka_id[]" multiple required style="height: 100px;">
                                                        <?php foreach($caraka as $c): ?>
                                                            <option value="<?= $c['id'] ?>"><?= esc($c['nama_lengkap']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="text-muted d-block mt-1">Tahan tombol CTRL untuk memilih banyak nama.</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Tanggal Penugasan</label>
                                                    <!-- Input date otomatis default ke hari ini (Sesuai setting zona waktu) -->
                                                    <input type="date" class="form-control" name="tgl_dijadwalkan" value="<?= date('Y-m-d') ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Tingkat Prioritas</label>
                                                    <select class="form-select" name="prioritas" required>
                                                        <option value="rendah">Rendah (Bisa ditunda)</option>
                                                        <option value="sedang" selected>Sedang (Normal)</option>
                                                        <option value="tinggi">Tinggi (Segera / Urgent)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Tugaskan Sekarang</button>
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