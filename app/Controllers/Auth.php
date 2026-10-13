<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function index()
    {
        // Jika sudah login, tendang ke dashboard masing-masing
        if (session()->get('isLoggedIn')) {
            return $this->redirectBasedOnRole(session()->get('role'));
        }
        
        // Tampilkan halaman login (nanti kita buat view-nya)
        return view('auth/login');
    }

    public function process()
    {
        $session = session();
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Cari user berdasarkan username
        $user = $userModel->where('username', $username)->first();

        if ($user) {
            // Jika user ketemu, cek apakah statusnya aktif
            if ($user['is_active'] != 1) {
                $session->setFlashdata('error', 'Akun Anda dinonaktifkan.');
                return redirect()->to('/login');
            }

            // Verifikasi password hash
            if (password_verify($password, $user['password'])) {
                // Set data session
                $sessData = [
                    'id'           => $user['id'],
                    'username'     => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'role'         => $user['role'],
                    'unit_id'      => $user['unit_id'],
                    'isLoggedIn'   => true
                ];
                $session->set($sessData);

                // Arahkan ke dashboard sesuai role
                return $this->redirectBasedOnRole($user['role']);
            } else {
                $session->setFlashdata('error', 'Password salah.');
                return redirect()->to('/login');
            }
        } else {
            $session->setFlashdata('error', 'Username tidak ditemukan.');
            return redirect()->to('/login');
        }
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login');
    }

    public function unauthorized()
    {
        return "Akses Ditolak. Anda tidak memiliki izin untuk membuka halaman ini.";
    }

    // Private function untuk routing otomatis berdasarkan role
    private function redirectBasedOnRole($role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'pimpinan':
                return redirect()->to('/pimpinan/dashboard');
            case 'pelapor':
                return redirect()->to('/pelapor/dashboard');
            case 'caraka':
                return redirect()->to('/caraka/dashboard');
            default:
                return redirect()->to('/login');
        }
    }
}