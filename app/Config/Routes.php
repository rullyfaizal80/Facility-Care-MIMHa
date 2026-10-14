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
});

// --- ROUTE PIMPINAN ---
$routes->group('pimpinan', ['filter' => 'roleFilter:pimpinan'], static function ($routes) {
    $routes->get('dashboard', function() {
        return '<h1>Dashboard Pimpinan</h1> Halo ' . session()->get('nama_lengkap') . '! <br> <a href="/logout">Logout</a>';
    });
});

// --- ROUTE PELAPOR ---
$routes->group('pelapor', ['filter' => 'roleFilter:pelapor'], static function ($routes) {
    $routes->get('dashboard', 'PelaporController::index');
    $routes->get('buat', 'PelaporController::buat');
    $routes->post('simpan', 'PelaporController::simpan');
    $routes->get('detail/(:num)', 'PelaporController::detail/$1');
});

// --- ROUTE CARAKA ---
$routes->group('caraka', ['filter' => 'roleFilter:caraka'], static function ($routes) {
    $routes->get('dashboard', 'CarakaController::index');
    $routes->get('kerjakan/(:num)', 'CarakaController::kerjakan/$1');
    $routes->post('selesaikan', 'CarakaController::selesaikan');
    $routes->get('riwayat', 'CarakaController::riwayat');
    $routes->get('detail/(:num)', 'CarakaController::detail/$1');
});
