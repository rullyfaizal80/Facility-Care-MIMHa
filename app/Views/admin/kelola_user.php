<?= $this->extend('layouts/dashboard_layout') ?>

<?= $this->section('sidebar_menu') ?>
    <li class="nav-item"><a class="nav-link" href="/admin/dashboard"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/tiket"><i class="fas fa-ticket-alt me-2"></i> Kelola Tiket</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/laporan"><i class="fas fa-print me-2"></i> Rekap & Cetak</a></li>
    <li class="nav-item"><a class="nav-link active" href="/admin/user"><i class="fas fa-users me-2"></i> Kelola User</a></li>
    <li class="nav-item"><a class="nav-link" href="/admin/laporan_saya"><i class="fas fa-bullhorn me-2"></i> Laporan Saya</a></li>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Kelola Pengguna Sistem</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-user-plus me-1"></i> Tambah Pengguna
        </button>
    </div>

    <!-- Alert Notifikasi -->
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

    <!-- Tabel Data User -->
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Role / Posisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach($users as $u): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-bold"><?= esc($u['nama_lengkap']) ?></td>
                            <td><?= esc($u['username']) ?></td>
                            <td>
                                <?php 
                                    $roleBadge = 'bg-secondary';
                                    if($u['role'] == 'admin') $roleBadge = 'bg-danger';
                                    if($u['role'] == 'pimpinan') $roleBadge = 'bg-primary';
                                    if($u['role'] == 'pelapor') $roleBadge = 'bg-info text-dark';
                                    if($u['role'] == 'caraka') $roleBadge = 'bg-success';
                                ?>
                                <span class="badge <?= $roleBadge ?> rounded-pill text-uppercase"><?= esc($u['role']) ?></span>
                            </td>
                            <td>
                                <?php if($u['is_active'] == 1): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fas fa-check-circle"></i> Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fas fa-ban"></i> Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Tombol Edit -->
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $u['id'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <!-- Tombol Hapus (Sembunyikan jika akun admin yang sedang login) -->
                                <?php if($u['id'] != session()->get('id')): ?>
                                    <a href="/admin/user/hapus/<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus pengguna ini secara permanen?')">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- MODAL EDIT USER -->
                        <div class="modal fade" id="modalEdit<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="/admin/user/edit" method="post">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Pengguna</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Nama Lengkap</label>
                                                <input type="text" class="form-control" name="nama_lengkap" value="<?= esc($u['nama_lengkap']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Username</label>
                                                <input type="text" class="form-control" name="username" value="<?= esc($u['username']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Password Baru (Opsional)</label>
                                                <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Role</label>
                                                <select class="form-select" name="role" required>
                                                    <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                    <option value="pimpinan" <?= $u['role'] == 'pimpinan' ? 'selected' : '' ?>>Pimpinan</option>
                                                    <option value="pelapor" <?= $u['role'] == 'pelapor' ? 'selected' : '' ?>>Pelapor (Guru/Staff)</option>
                                                    <option value="caraka" <?= $u['role'] == 'caraka' ? 'selected' : '' ?>>Caraka (Teknisi)</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Status Akun</label>
                                                <select class="form-select" name="is_active" required>
                                                    <option value="1" <?= $u['is_active'] == 1 ? 'selected' : '' ?>>Aktif (Bisa Login)</option>
                                                    <option value="0" <?= $u['is_active'] == 0 ? 'selected' : '' ?>>Nonaktif (Blokir)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Perubahan</button>
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

    <!-- MODAL TAMBAH USER -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="/admin/user/tambah" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Pengguna Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Username</label>
                            <input type="text" class="form-control" name="username" placeholder="Buat username (tanpa spasi)" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Buat kata sandi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="" disabled selected>-- Pilih Posisi --</option>
                                <option value="admin">Admin</option>
                                <option value="pimpinan">Pimpinan</option>
                                <option value="pelapor">Pelapor (Guru/Staff)</option>
                                <option value="caraka">Caraka (Teknisi)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Tambah User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>