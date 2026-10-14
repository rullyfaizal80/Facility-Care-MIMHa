<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Perawatan - Facility Care MIMHa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #ffffff; color: #000000; font-family: Arial, sans-serif; }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        
        /* ATURAN PENTING: Menyembunyikan elemen saat dicetak */
        @media print {
            .no-print { 
                display: none !important; 
            }
            body { 
                padding: 0 !important; 
                margin: 0 !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container my-4">
        <!-- Tombol Aksi (Ditambahkan class khusus agar mutlak hilang saat print) -->
        <div class="d-flex justify-content-between mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Dokumen</button>
            <button onclick="window.close()" class="btn btn-secondary">Tutup Halaman</button>
        </div>

        <!-- Kop Surat -->
        <div class="text-center kop-surat">
            <h4 class="fw-bold mb-1">YAYASAN FATHUL HUDA BANDUNG</h4>
            <h3 class="fw-bold mb-1">FACILITY CARE MIMHA</h3>
            <p class="mb-0 small text-muted">Laporan Resmi Pemeliharaan & Perbaikan Sarana Prasarana</p>
        </div>

        <?php
            $arrBulan = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
            $namaBulan = $arrBulan[$bulan] ?? '';
        ?>

        <!-- Informasi Periode -->
        <div class="mb-3">
            <table class="table table-borderless table-sm w-auto">
                <tr><td><strong>Periode Bulan</strong></td><td>: <?= $namaBulan ?> <?= $tahun ?></td></tr>
                <tr><td><strong>Filter Status</strong></td><td>: <?= $status ? str_replace('_', ' ', strtoupper($status)) : 'Semua Status' ?></td></tr>
                <tr><td><strong>Tanggal Cetak</strong></td><td>: <?= date('d-m-Y H:i') ?></td></tr>
            </table>
        </div>

        <!-- Tabel Data -->
        <table class="table table-bordered align-middle small">
            <thead class="table-light text-center">
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="15%">Pelapor</th>
                    <th width="20%">Lokasi & Masalah</th>
                    <th width="15%">Status</th>
                    <th width="15%">Teknisi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($tiket)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-3">Tidak ada data laporan.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach($tiket as $t): ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($t['tgl_dibuat'])) ?></td>
                            <td><?= esc($t['nama_pelapor'] ?? 'Sistem') ?></td>
                            <td>
                                <strong><?= esc($t['lokasi_manual']) ?></strong><br>
                                <span class="text-muted"><?= esc($t['judul_laporan']) ?></span>
                            </td>
                            <td class="text-center fw-bold"><?= str_replace('_', ' ', strtoupper($t['status'])) ?></td>
                            <td><?= esc($t['nama_caraka']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Tanda Tangan -->
        <div class="row mt-5">
            <div class="col-8"></div>
            <div class="col-4 text-center">
                <p class="mb-5">Mengetahui,<br><strong>Pimpinan</strong></p>
                <br><br>
                <p class="mb-0"><strong>( ......................................... )</strong></p>
            </div>
        </div>
    </div>

</body>
</html>