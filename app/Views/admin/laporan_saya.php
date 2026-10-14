<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item"><a class="nav-link" href="/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/tiket"><i class="fas fa-ticket-alt me-2"></i> Kelola Tiket</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/laporan"><i class="fas fa-print me-2"></i> Rekap & Cetak</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/user"><i class="fas fa-users me-2"></i> Kelola User</a></li>
    <li class="nav-item"><a class="nav-link active" href="/admin/laporan_saya"><i class="fas fa-bullhorn me-2"></i> Laporan Saya</a></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-4 border-bottom">
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

    <!-- Tampilan Card List -->
    <div class="row">
        <?php if(empty($tiket)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <div class="bg-light rounded-circle d-inline-block p-4 mb-3 shadow-sm">
                    <i class="fas fa-box-open fa-3x text-secondary"></i>
                </div>
                <h6 class="fw-bold">Belum ada laporan</h6>
                <p class="small">Anda belum membuat laporan kerusakan apapun.</p>
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

    <!-- MODAL FORM BUAT LAPORAN (DISAMAKAN DENGAN FORMAT PELAPOR) -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <form action="/admin/laporan_saya/simpan" method="post" enctype="multipart/form-data">
                    <div class="modal-header bg-white border-bottom px-4 pt-4">
                        <h5 class="modal-title fw-bold">Buat Laporan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
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

                       <div class="mb-3">
                            <label class="form-label fw-bold">Foto Kondisi <span class="text-danger">*</span></label>
                            <input class="form-control" type="file" id="foto" name="foto" accept="image/*" capture="environment" required>
                            <small class="text-muted mt-1 d-block">Wajib mengambil foto langsung atau memilih dari galeri.</small>
                        </div>

                    </div>
                    <div class="modal-footer bg-white border-top px-4 pb-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="fas fa-paper-plane me-2"></i> Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>