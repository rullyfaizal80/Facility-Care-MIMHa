<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array'; 
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['username', 'password', 'nama_lengkap', 'role', 'unit_id', 'is_active'];

    // Timestamps dimatikan karena di tabel users kita tidak pakai created_at/updated_at
    protected $useTimestamps    = false; 
}