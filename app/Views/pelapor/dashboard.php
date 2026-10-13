<?= $this->extend('layouts/mobile_layout') ?>

<?= $this->section('content') ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="m-0 fw-bold">Laporan Saya</h5>
    </div>

   <!-- FITUR FILTER PER BULAN (Auto-Submit) -->
    <div class="card shadow-sm border-0 mb-3 bg-light">
        <div class="card-body p-2">
            <form action="/pelapor/dashboard" method="get" id="formFilterBulan" class="d-flex gap-2">
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
                        // Menampilkan 4 tahun ke belakang + tahun berjalan (Total 5 tahun)
                        for($i = $tahun_berjalan; $i >= $tahun_berjalan - 4; $i--): 
                    ?>
                        <option value="<?= $i ?>" <?= ($tahun_aktif == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                <!-- Tombol submit dihapus karena kita pakai Javascript auto-submit -->
            </form>
        </div>
    </div>

    <!-- Tampilkan Notifikasi -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- LIST TIKET -->
    <?php if(empty($tiket)): ?>
        <div class="text-center mt-5 text-muted">
            <i class="fas fa-clipboard-check fa-3x mb-2"></i>
            <p>Tidak ada laporan di bulan ini.</p>
        </div>
    <?php else: ?>
        <?php foreach($tiket as $t): ?>
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="card-title fw-bold text-primary mb-1"><?= esc($t['judul_laporan']) ?></h6>
                        <?php 
                            $badge = 'bg-secondary';
                            if($t['status'] == 'menunggu_respon') $badge = 'bg-warning text-dark';
                            if($t['status'] == 'dikerjakan') $badge = 'bg-info text-dark';
                            if($t['status'] == 'menunggu_approval') $badge = 'bg-primary';
                            if($t['status'] == 'selesai') $badge = 'bg-success';
                        ?>
                        <span class="badge <?= $badge ?> rounded-pill" style="font-size: 0.65rem;">
                            <?= str_replace('_', ' ', strtoupper($t['status'])) ?>
                        </span>
                    </div>
                    
                    <p class="card-text text-muted mb-1 small">
                        <i class="fas fa-map-marker-alt me-1"></i> <?= esc($t['lokasi_manual']) ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-end mt-2">
    <small class="text-muted" style="font-size: 0.7rem;">
        <!-- Tanggal Dibuat (Selalu Tampil) -->
        <i class="fas fa-calendar-alt"></i> Dibuat: <?= date('d M Y', strtotime($t['tgl_dibuat'])) ?>
        
        <!-- Tanggal Selesai (Tambahan jika sudah selesai) -->
        <?php if($t['status'] == 'selesai' && !empty($t['tgl_selesai'])): ?>
            <br>
            <span class="text-success fw-bold"><i class="fas fa-check-double"></i> Selesai: <?= date('d M Y', strtotime($t['tgl_selesai'])) ?></span>
        <?php endif; ?>
    </small>
    <!-- TOMBOL MENUJU DETAIL -->
    <a href="/pelapor/detail/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary py-0" style="font-size: 0.75rem; height: fit-content;">Detail</a>
</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('bottom_nav') ?>
    <a href="/pelapor/dashboard" class="active"><i class="fas fa-home"></i>Beranda</a>
    <a href="/pelapor/buat"><i class="fas fa-plus-circle text-primary fs-3"></i>Lapor</a>
    <a href="/logout"><i class="fas fa-sign-out-alt"></i>Keluar</a>
<?= $this->endSection() ?>

<!-- TAMBAHKAN BLOK SCRIPT INI DI BAWAH -->
<?= $this->section('scripts') ?>
    <script>
        // Ambil elemen select
        const filterBulan = document.getElementById('filterBulan');
        const filterTahun = document.getElementById('filterTahun');
        const formFilter = document.getElementById('formFilterBulan');

        // Tambahkan event listener saat value berubah
        filterBulan.addEventListener('change', function() {
            formFilter.submit(); // Submit form otomatis
        });

        filterTahun.addEventListener('change', function() {
            formFilter.submit(); // Submit form otomatis
        });
    </script>
<?= $this->endSection() ?>