<?php
namespace App\Models;
use CodeIgniter\Model;

class TiketModel extends Model
{
    protected $table         = 'tiket_perawatan';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
    'jenis_tiket', 'judul_laporan', 'lokasi_manual', 'deskripsi', 'fasilitas_id',
        'pelapor_id', 'caraka_id', 'status', 'prioritas', 
        'tgl_dibuat', 'tgl_dijadwalkan', 'tgl_selesai',
        'catatan_admin' // <--- Tambahkan ini
    ];
    // Karena kita pakai field tgl_dibuat manual (bukan created_at), matikan timestamps bawaan
    protected $useTimestamps = false; 
}