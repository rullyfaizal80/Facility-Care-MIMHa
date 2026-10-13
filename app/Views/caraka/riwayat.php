<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <h5 class="mb-3 fw-bold">Riwayat Selesai</h5>

    <!-- FITUR FILTER PER BULAN (Auto-Submit) -->
    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body p-2">
            <form action="/caraka/riwayat" method="get" id="formFilterBulan" class="d-flex gap-2">
                <select name="bulan" id="filterBulan" class="form-select form-select-sm">
                    <?php 
                        $nama_bulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                        foreach($nama_bulan as $num => $nama): 
                    ?>
                        <option value="<?= $num ?>" <?= ($bulan_aktif == $num) ? 'selected' : '' ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
                
                <select name="tahun" id="filterTahun" class="form-select form-select-sm">
                    <?php 
                        $tahun_berjalan = date('Y');
                        for($i = $tahun_berjalan; $i >= $tahun_berjalan - 4; $i--): 
                    ?>
                        <option value="<?= $i ?>" <?= ($tahun_aktif == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if(empty($tugas)): ?>
        <div class="text-center mt-5 text-muted">
            <i class="fas fa-history fa-3x mb-3 text-secondary"></i>
            <p>Tidak ada riwayat pekerjaan selesai di bulan ini.</p>
        </div>
    <?php else: ?>
        <?php foreach($tugas as $t): ?>
            <div class="card mb-3 shadow-sm border-0 border-start border-success border-4 opacity-75">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="card-title fw-bold text-dark mb-1"><?= esc($t['judul_laporan']) ?></h6>
                        <span class="badge bg-success rounded-pill"><i class="fas fa-check-double"></i> SELESAI</span>
                    </div>
                    <p class="card-text mb-1 small text-muted">
                        <i class="fas fa-map-marker-alt"></i> <?= esc($t['lokasi_manual']) ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-end mt-3 border-top pt-2">
                        <small class="text-muted" style="font-size: 0.7rem;">
                            <i class="fas fa-calendar-check"></i> Disetujui:<br><?= date('d M Y', strtotime($t['tgl_selesai'])) ?>
                        </small>
                        <a href="/caraka/detail/<?= $t['id'] ?>" class="btn btn-sm btn-outline-success py-0" style="font-size: 0.75rem; height: fit-content;">Lihat Detail</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/caraka/dashboard"><i class="fas fa-home"></i>Beranda</a>
    <a href="/caraka/riwayat" class="active"><i class="fas fa-clipboard-list"></i>Riwayat</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        const filterBulan = document.getElementById('filterBulan');
        const filterTahun = document.getElementById('filterTahun');
        const formFilter = document.getElementById('formFilterBulan');

        filterBulan.addEventListener('change', function() { formFilter.submit(); });
        filterTahun.addEventListener('change', function() { formFilter.submit(); });
    </script>
<?= $this->endSection() ?>