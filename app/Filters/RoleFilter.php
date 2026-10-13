<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        
        // 1. Cek apakah user sudah login. Jika belum, lempar kembali ke halaman login.
        if (!$session->get('isLoggedIn')) {
            $session->setFlashdata('error', 'Akses ditolak. Silakan login terlebih dahulu.');
            return redirect()->to('/login');
        }

        // 2. Cek apakah role user saat ini diizinkan mengakses URL yang dituju.
        // $arguments berisi role yang diizinkan (ditentukan di file Routes.php nanti)
        if ($arguments && !in_array($session->get('role'), $arguments)) {
            return redirect()->to('/unauthorized'); 
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Dikosongkan karena kita tidak butuh memproses sesuatu setelah halaman dirender
    }
}