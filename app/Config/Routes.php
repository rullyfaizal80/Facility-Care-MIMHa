<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// --- ROUTE AUTENTIKASI ---
$routes->get('/', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('login/process', 'Auth::process');
$routes->get('logout', 'Auth::logout');
$routes->get('unauthorized', 'Auth::unauthorized');

// --- ROUTE ADMIN ---
$routes->group('admin', ['filter' => 'roleFilter:admin'], static function ($routes) {
    $routes->get('dashboard', 'AdminController::index');
    $routes->get('tiket', 'AdminController::tiket');
    $routes->post('tiket/tugaskan', 'AdminController::tugaskan');
    $routes->get('tiket/detail/(:num)', 'AdminController::detailTiket/$1');
    $routes->post('tiket/approval', 'AdminController::prosesApproval');
    $routes->post('tiket/update', 'AdminController::updateTiket');
    $routes->get('laporan', 'AdminController::laporan');
    $routes->get('laporan/cetak', 'AdminController::cetak');
    $routes->get('user', 'AdminController::users');
    $routes->post('user/tambah', 'AdminController::tambahUser');
    $routes->post('user/edit', 'AdminController::editUser');
    $routes->get('user/hapus/(:num)', 'AdminController::hapusUser/$1');
    $routes->get('laporan_saya', 'AdminController::laporanSaya');
    $routes->post('laporan_saya/simpan', 'AdminController::simpanLaporanSaya');
    $routes->get('renderFoto/(:any)', 'AdminController::renderFoto/$1');
});

// --- ROUTE PIMPINAN ---
$routes->group('pimpinan', ['filter' => 'roleFilter:pimpinan'], static function($routes) { 
    $routes->get('dashboard', 'PimpinanController::index');
    $routes->get('laporan', 'PimpinanController::laporan');
    $routes->get('laporan/cetak', 'PimpinanController::cetak');
    $routes->get('tiket/detail/(:num)', 'PimpinanController::detail/$1');
    $routes->get('renderFoto/(:any)', 'PimpinanController::renderFoto/$1');
});

// --- ROUTE PELAPOR ---
$routes->group('pelapor', ['filter' => 'roleFilter:pelapor'], static function ($routes) {
    $routes->get('dashboard', 'PelaporController::index');
    $routes->get('buat', 'PelaporController::buat');
    $routes->post('simpan', 'PelaporController::simpan');
    $routes->get('detail/(:num)', 'PelaporController::detail/$1');
    $routes->get('renderFoto/(:any)', 'PelaporController::renderFoto/$1');
});

// --- ROUTE CARAKA ---
$routes->group('caraka', ['filter' => 'roleFilter:caraka'], static function ($routes) {
    $routes->get('dashboard', 'CarakaController::index');
    $routes->get('kerjakan/(:num)', 'CarakaController::kerjakan/$1');
    $routes->post('selesaikan', 'CarakaController::selesaikan');
    $routes->get('riwayat', 'CarakaController::riwayat');
    $routes->get('detail/(:num)', 'CarakaController::detail/$1');
});
