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
                        <th>Teknisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tiket as $t): ?>
                        <tr>
                            <td>
                                <div class="small" style="white-space: nowrap;">
                                    <div class="mb-1 text-dark">
                                        <i class="fas fa-bullhorn text-muted me-1"></i><?= date('d M Y, H:i', strtotime($t['tgl_dibuat'])) ?>
                                    </div>
                                    <?php if(!empty($t['tgl_selesai'])): ?>
                                    <div class="text-success fw-bold">
                                        <i class="fas fa-check-double me-1"></i><?= date('d M Y, H:i', strtotime($t['tgl_selesai'])) ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="text-muted"><i class="fas fa-clock me-1"></i>-</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= esc($t['nama_pelapor'] ?? 'Sistem (Rutin)') ?></td>
                            <td><strong><?= esc($t['lokasi_manual']) ?></strong></td>
                            <td><?= esc($t['judul_laporan']) ?></td>
                            
                            <!-- LOGIKA WARNA STATUS -->
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($t['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                                    if($t['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                                    if($t['status'] == 'pending') $badge = 'bg-danger'; 
                                    if($t['status'] == 'menunggu_approval') $badge = 'bg-primary';
                                    if($t['status'] == 'selesai') $badge = 'bg-success';
                                    if($t['status'] == 'ditolak') $badge = 'bg-dark'; 
                                ?>
                                <span class="badge <?= $badge ?> rounded-pill"><?= str_replace('_', ' ', strtoupper($t['status'])) ?></span>
                            </td>
                            
                            <td><small class="text-muted fw-bold"><?= esc($t['nama_caraka']) ?></small></td>
                            
                            <!-- LOGIKA TOMBOL AKSI -->
                            <td>
                                <?php if($t['status'] == 'menunggu_respon'): ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAssign<?= $t['id'] ?>">
                                        <i class="fas fa-user-plus"></i> Tugaskan
                                    </button>
                                <?php else: ?>
                                    <?php if($t['status'] == 'menunggu_approval'): ?>
                                        <a href="/admin/tiket/detail/<?= $t['id'] ?>" class="btn btn-sm btn-warning fw-bold text-dark"><i class="fas fa-search"></i> Review</a>
                                    <?php elseif($t['status'] == 'selesai'): ?>
                                        <a href="/admin/tiket/detail/<?= $t['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-check-double"></i> Selesai</a>
                                    <?php endif; ?>
                                    
                                    <!-- Tombol Edit -->
                                    <button type="button" class="btn btn-sm btn-outline-info <?= ($t['status'] == 'dikerjakan' || $t['status'] == 'pending') ? '' : 'ms-1' ?>" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $t['id'] ?>" title="Edit Tiket">
                                        <i class="fas fa-edit"></i> <?= ($t['status'] == 'dikerjakan' || $t['status'] == 'pending') ? 'Edit / Pending' : '' ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- MODAL ASSIGN (TIKET BARU) -->
                        <div class="modal fade" id="modalAssign<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="/admin/tiket/tugaskan" method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Penugasan Tiket Baru</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body row">
                                            <input type="hidden" name="tiket_id" value="<?= $t['id'] ?>">
                                            <div class="col-md-6 border-end">
                                                <h6 class="fw-bold text-primary mb-1"><?= esc($t['judul_laporan']) ?></h6>
                                                <p class="small mb-2"><i class="fas fa-map-marker-alt text-muted"></i> <?= esc($t['lokasi_manual']) ?></p>
                                                <p class="small mb-3 text-muted">"<?= esc($t['deskripsi']) ?>"</p>
                                                <small class="fw-bold d-block mb-1">Foto Kondisi:</small>
                                                <?php if($t['foto_sebelum']): ?>
                                                    <?php $nama_file = str_replace('LOKAL_', '', $t['foto_sebelum']); ?>
                                                    <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" class="img-fluid rounded border" alt="Foto" style="max-height: 180px; cursor: zoom-in;" onclick="showImage(this.src)">
                                                <?php else: ?>
                                                    <div class="bg-light p-3 text-center text-muted small rounded border">Tidak ada foto</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Pilih Teknisi (Ceklis)</label>
                                                    <div class="border rounded p-2 bg-light" style="max-height: 120px; overflow-y: auto;">
                                                        <?php foreach($caraka as $c): ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="caraka_id[]" value="<?= $c['id'] ?>" id="assign_<?= $t['id'] ?>_<?= $c['id'] ?>">
                                                                <label class="form-check-label small" for="assign_<?= $t['id'] ?>_<?= $c['id'] ?>"><?= esc($c['nama_lengkap']) ?></label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Tanggal Penugasan</label>
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
                        
                        <!-- MODAL EDIT (UPDATE STATUS) -->
<div class="modal fade" id="modalEdit<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/admin/tiket/update" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tiket: <?= esc($t['judul_laporan']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body row">
                    <input type="hidden" name="tiket_id" value="<?= $t['id'] ?>">
                    
                    <!-- Kiri -->
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-primary mb-1"><?= esc($t['judul_laporan']) ?></h6>
                        <p class="small mb-2"><i class="fas fa-map-marker-alt text-muted"></i> <?= esc($t['lokasi_manual']) ?></p>
                        <p class="small mb-3 text-muted">"<?= esc($t['deskripsi']) ?>"</p>
                        
                        <!-- Tambahan: Menampilkan Foto di Modal Edit -->
                        <small class="fw-bold d-block mb-1">Foto Kondisi:</small>
                        <?php if($t['foto_sebelum']): ?>
                            <?php $nama_file = str_replace('LOKAL_', '', $t['foto_sebelum']); ?>
                            <img src="<?= base_url('uploads/laporan/' . $nama_file) ?>" class="img-fluid rounded border" alt="Foto" style="max-height: 180px; cursor: zoom-in;" onclick="showImage(this.src)">
                        <?php else: ?>
                            <div class="bg-light p-3 text-center text-muted small rounded border">Tidak ada foto</div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Kanan (Sudah direvisi dengan kolom catatan) -->
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label fw-bold small">Ubah Status</label>
                            <select class="form-select form-select-sm" name="status" required>
                                <option value="dikerjakan" <?= $t['status'] == 'dikerjakan' ? 'selected' : '' ?>>Dikerjakan (Kembalikan ke Caraka)</option>
                                <option value="pending" <?= $t['status'] == 'pending' ? 'selected' : '' ?>>Pending (Ditunda)</option>
                                <option value="menunggu_approval" <?= $t['status'] == 'menunggu_approval' ? 'selected' : '' ?>>Menunggu Approval</option>
                                <option value="selesai" <?= $t['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="ditolak" <?= $t['status'] == 'ditolak' ? 'selected' : '' ?>>Ditolak (Batalkan Laporan)</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold small">Ubah/Tambah Teknisi (Ceklis)</label>
                            <div class="border rounded p-1 bg-light" style="max-height: 90px; overflow-y: auto;">
                                <?php 
                                    $assigned_caraka = explode(',', $t['caraka_id'] ?? '');
                                    foreach($caraka as $c): 
                                        $isChecked = in_array($c['id'], $assigned_caraka) ? 'checked' : '';
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="caraka_id[]" value="<?= $c['id'] ?>" id="edit_<?= $t['id'] ?>_<?= $c['id'] ?>" <?= $isChecked ?>>
                                        <label class="form-check-label small" for="edit_<?= $t['id'] ?>_<?= $c['id'] ?>"><?= esc($c['nama_lengkap']) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label fw-bold small">Tgl Tugas</label>
                                <input type="date" class="form-control form-control-sm" name="tgl_dijadwalkan" value="<?= $t['tgl_dijadwalkan'] ?? date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label fw-bold small">Prioritas</label>
                                <select class="form-select form-select-sm" name="prioritas" required>
                                    <option value="rendah" <?= $t['prioritas'] == 'rendah' ? 'selected' : '' ?>>Rendah</option>
                                    <option value="sedang" <?= $t['prioritas'] == 'sedang' ? 'selected' : '' ?>>Sedang</option>
                                    <option value="tinggi" <?= $t['prioritas'] == 'tinggi' ? 'selected' : '' ?>>Tinggi</option>
                                </select>
                            </div>
                        </div>
                        <!-- TAMBAHAN: Catatan Admin -->
                        <div class="mb-2">
                            <label class="form-label fw-bold small">Catatan (Alasan Pending/Ditolak)</label>
                            <textarea class="form-control form-control-sm" name="catatan_admin" rows="2" placeholder="Ketik alasan di sini..."><?= esc($t['catatan_admin'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-info text-white"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
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